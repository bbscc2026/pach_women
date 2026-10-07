<?php

namespace Tests\Feature;

use App\Livewire\Checkout;
use App\Livewire\ProductShow;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use App\Support\Cart;
use Database\Seeders\ContentPageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class ShopTest extends TestCase
{
    use RefreshDatabase;

    private function product(array $attributes = []): Product
    {
        $category = Category::create(['name' => 'Kurtis', 'slug' => 'kurtis']);

        return $category->products()->create([
            'name' => 'Test Kurti',
            'slug' => 'test-kurti',
            'price' => 1000,
            'sizes' => ['S', 'M'],
            'stock' => 5,
            ...$attributes,
        ])->fresh();
    }

    private function checkoutAs(User $user, string $method)
    {
        return Livewire::actingAs($user)
            ->test(Checkout::class)
            ->set('name', 'Asha')
            ->set('phone', '9876543210')
            ->set('line1', '12 MG Road')
            ->set('city', 'Kozhikode')
            ->set('state', 'Kerala')
            ->set('pincode', '673001')
            ->set('paymentMethod', $method)
            ->call('placeOrder');
    }

    public function test_storefront_pages_load(): void
    {
        $product = $this->product();
        $this->seed(ContentPageSeeder::class);

        $this->get('/')->assertOk()->assertSee('Test Kurti');
        $this->get('/shop')->assertOk();
        $this->get('/category/kurtis')->assertOk();
        $this->get('/product/'.$product->slug)->assertOk()->assertSee('₹1,000');
        $this->get('/cart')->assertOk();
        $this->get('/pages/returns')->assertOk()->assertSee('Returns &amp; refunds', false);
        $this->get('/pages/contact')->assertOk()->assertSee(config('shop.contact.email'));
        $this->get('/pages/does-not-exist')->assertNotFound();
        $this->get('/checkout')->assertRedirect('/login');
    }

    public function test_account_and_checkout_pages_load(): void
    {
        $product = $this->product();
        $user = User::factory()->create();
        app(Cart::class)->add($product, 'M', 1);
        $this->checkoutAs($user, 'cod');
        $order = Order::sole();
        $order->update(['status' => 'shipped', 'tracking_number' => 'TRK123']);

        app(Cart::class)->add($product, 'S', 1);
        $this->actingAs($user)->get('/checkout')->assertOk()->assertSee('Delivery address');
        $this->actingAs($user)->get('/account/orders')->assertOk()->assertSee($order->number);
        $this->actingAs($user)->get('/account/orders/'.$order->number)->assertOk()->assertSee('TRK123')->assertSee('Shipped');
        $this->actingAs($user)->get('/account/addresses')->assertOk()->assertSee('12 MG Road');
        $this->actingAs($user)->get('/account/profile')->assertOk();
        $this->actingAs($user)->get('/order/'.$order->number.'/thank-you')->assertOk();
    }

    public function test_size_is_required_before_adding_to_cart(): void
    {
        $product = $this->product();

        Livewire::test(ProductShow::class, ['product' => $product])
            ->call('addToCart')
            ->assertHasErrors('size')
            ->assertDispatched('size-required')
            ->call('selectSize', 'XXL')
            ->assertSet('size', null)
            ->call('selectSize', 'M')
            ->call('addToCart')
            ->assertHasNoErrors()
            ->assertDispatched('cart-updated')
            ->assertDispatched('open-cart');

        $this->assertSame(1, app(Cart::class)->count());
    }

    public function test_cod_checkout_creates_order_and_reduces_stock(): void
    {
        $product = $this->product();
        $user = User::factory()->create();
        app(Cart::class)->add($product, 'M', 2);

        $this->checkoutAs($user, 'cod')->assertHasNoErrors()->assertRedirect();

        $order = Order::with('items')->sole();
        $this->assertSame('confirmed', $order->status);
        $this->assertEquals(2000, (float) $order->total); // over the free-shipping threshold
        $this->assertSame('M', $order->items->first()->size);
        $this->assertSame(3, $product->fresh()->stock);
        $this->assertSame(0, app(Cart::class)->count());

        // Cancelling puts the stock back.
        $order->update(['status' => 'cancelled']);
        $this->assertSame(5, $product->fresh()->stock);
    }

    public function test_razorpay_payment_is_verified_by_signature(): void
    {
        config(['shop.razorpay.key' => 'rzp_test_key', 'shop.razorpay.secret' => 'test_secret']);
        Http::fake(['api.razorpay.com/*' => Http::response(['id' => 'order_RZP123'])]);

        $product = $this->product();
        $user = User::factory()->create();
        app(Cart::class)->add($product, 'S', 1);

        $this->checkoutAs($user, 'razorpay')->assertDispatched('razorpay-open');

        $order = Order::sole();
        $this->assertSame('order_RZP123', $order->razorpay_order_id);
        $this->assertSame(5, $product->fresh()->stock, 'Stock is not taken until payment succeeds.');

        // A forged signature is rejected.
        $this->actingAs($user)->post('/payment/razorpay/verify', [
            'order' => $order->number,
            'razorpay_order_id' => 'order_RZP123',
            'razorpay_payment_id' => 'pay_ABC',
            'razorpay_signature' => 'forged',
        ])->assertRedirect('/checkout');
        $this->assertSame('failed', $order->fresh()->payment_status);

        // A valid signature marks the order paid.
        $this->actingAs($user)->post('/payment/razorpay/verify', [
            'order' => $order->number,
            'razorpay_order_id' => 'order_RZP123',
            'razorpay_payment_id' => 'pay_ABC',
            'razorpay_signature' => hash_hmac('sha256', 'order_RZP123|pay_ABC', 'test_secret'),
        ])->assertRedirect(route('order.placed', $order));

        $order->refresh();
        $this->assertSame('paid', $order->payment_status);
        $this->assertSame('confirmed', $order->status);
        $this->assertSame(4, $product->fresh()->stock);
    }

    public function test_customers_cannot_see_other_customers_orders_or_admin(): void
    {
        $product = $this->product();
        $owner = User::factory()->create();
        $other = User::factory()->create();
        app(Cart::class)->add($product, 'M', 1);
        $this->checkoutAs($owner, 'cod');
        $order = Order::sole();

        $this->actingAs($other)->get('/account/orders/'.$order->number)->assertNotFound();
        $this->actingAs($other)->get('/order/'.$order->number.'/thank-you')->assertNotFound();
        $this->actingAs($other)->get('/admin')->assertForbidden();
    }

    public function test_admin_settings_override_config_and_fill_page_placeholders(): void
    {
        $this->seed(ContentPageSeeder::class);

        Setting::saveMany([
            'contact.phone' => '+91 90000 11111',
            'free_shipping_over' => 999.0,
            'cod_enabled' => false,
            'razorpay.secret' => 'shh',
            'announcements' => ['Diwali sale is live'],
        ]);
        Setting::applyToConfig();

        $this->assertSame('+91 90000 11111', config('shop.contact.phone'));
        $this->assertFalse(config('shop.cod_enabled'));
        $this->assertSame('shh', config('shop.razorpay.secret'));
        $this->assertNotSame('shh', Setting::find('razorpay.secret')->value, 'Secret is encrypted at rest.');

        $this->get('/pages/returns')->assertSee('+91 90000 11111');
        $this->get('/pages/shipping')->assertSee('₹999');
        $this->get('/')->assertSee('Diwali sale is live');
    }

    public function test_admin_can_open_settings_and_pages(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->seed(ContentPageSeeder::class);

        $this->actingAs($admin)->get('/admin')->assertOk()->assertSee('Recent orders');
        $this->actingAs($admin)->get('/admin/site-settings')->assertOk()->assertSee('Announcement bar');
        $this->actingAs($admin)->get('/admin/content-pages')->assertOk()->assertSee('Shipping policy');
    }
}
