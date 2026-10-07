<?php

namespace Tests\Feature;

use App\Filament\Resources\Categories\Pages\EditCategory;
use App\Livewire\Checkout;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Support\Cart;
use Filament\Actions\DeleteAction;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class OrderIntegrityTest extends TestCase
{
    use RefreshDatabase;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->category = Category::create(['name' => 'Kurtis', 'slug' => 'kurtis']);
        config(['shop.razorpay.key' => 'rzp_test_key', 'shop.razorpay.secret' => 'api_secret', 'shop.razorpay.webhook_secret' => 'hook_secret']);
    }

    private function product(int $stock = 5): Product
    {
        return $this->category->products()->create([
            'name' => 'Kurti', 'slug' => 'kurti-'.uniqid(), 'price' => 1000, 'stock' => $stock, 'sizes' => ['M'],
        ])->fresh();
    }

    private function order(Product $product, string $method, int $quantity = 2, array $attributes = []): Order
    {
        $order = Order::create([
            'payment_method' => $method, 'status' => 'pending', 'payment_status' => 'pending',
            'subtotal' => 1000 * $quantity, 'shipping' => 0, 'total' => 1000 * $quantity,
            'name' => 'Asha', 'email' => 'asha@example.com', 'phone' => '9876543210',
            'line1' => '12 MG Road', 'city' => 'Kozhikode', 'state' => 'Kerala', 'pincode' => '673001',
            ...$attributes,
        ]);
        $order->items()->create(['product_id' => $product->id, 'name' => $product->name, 'size' => 'M', 'price' => 1000, 'quantity' => $quantity]);

        return $order;
    }

    private function webhook(array $event, ?string $secret = 'hook_secret')
    {
        $body = json_encode($event);

        return $this->call('POST', '/payment/razorpay/webhook', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_RAZORPAY_SIGNATURE' => hash_hmac('sha256', $body, (string) $secret),
        ], $body);
    }

    private function paymentEvent(Order $order, int $amount): array
    {
        return [
            'event' => 'payment.captured',
            'payload' => ['payment' => ['entity' => [
                'id' => 'pay_123', 'order_id' => $order->razorpay_order_id, 'amount' => $amount, 'currency' => 'INR',
            ]]],
        ];
    }

    public function test_webhook_marks_online_order_paid_even_if_customer_never_returns(): void
    {
        $product = $this->product();
        $order = $this->order($product, 'razorpay', 2, ['razorpay_order_id' => 'order_ABC']);

        $this->webhook($this->paymentEvent($order, 1000), 'wrong_secret')->assertStatus(400);
        $this->assertSame('pending', $order->fresh()->payment_status);

        $this->webhook($this->paymentEvent($order, 100))->assertStatus(422);
        $this->assertSame('pending', $order->fresh()->payment_status, 'Partial amount is rejected.');

        $this->webhook($this->paymentEvent($order, 200000))->assertOk();
        $order->refresh();
        $this->assertSame('paid', $order->payment_status);
        $this->assertSame('confirmed', $order->status);
        $this->assertSame('pay_123', $order->razorpay_payment_id);
        $this->assertSame(3, $product->fresh()->stock);

        // Razorpay retries webhooks, and the browser redirect can arrive too: stock is taken only once.
        $this->webhook($this->paymentEvent($order, 200000))->assertOk();
        $this->assertSame(3, $product->fresh()->stock);
    }

    public function test_cancel_and_uncancel_move_stock_exactly_once(): void
    {
        $product = $this->product(5);
        $order = $this->order($product, 'cod');
        $order->update(['status' => 'confirmed']);
        $order->reserveStock();
        $order->reserveStock();
        $this->assertSame(3, $product->fresh()->stock);

        $order->update(['status' => 'cancelled']);
        $this->assertSame(5, $product->fresh()->stock);

        $order->restoreStock();
        $this->assertSame(5, $product->fresh()->stock, 'Restoring twice does not add extra stock.');

        $order->update(['status' => 'confirmed']);
        $this->assertSame(3, $product->fresh()->stock, 'Un-cancelling takes the stock again.');
    }

    public function test_unpaid_online_order_cancelled_and_reinstated_does_not_touch_stock(): void
    {
        $product = $this->product(5);
        $order = $this->order($product, 'razorpay');

        $order->update(['status' => 'cancelled']);
        $order->update(['status' => 'pending']);

        $this->assertSame(5, $product->fresh()->stock);
        $this->assertSame('Awaiting payment', $order->fresh()->statusLabel());
    }

    public function test_checkout_rejects_when_stock_ran_out_after_adding_to_cart(): void
    {
        $product = $this->product(2);
        $user = User::factory()->create();
        app(Cart::class)->add($product, 'M', 2);

        // Someone else buys one piece meanwhile.
        $product->update(['stock' => 1]);

        Livewire::actingAs($user)->test(Checkout::class)
            ->set('name', 'Asha')->set('phone', '9876543210')->set('line1', '12 MG Road')
            ->set('city', 'Kozhikode')->set('pincode', '673001')->set('paymentMethod', 'cod')
            ->call('placeOrder')
            ->assertHasErrors('cart');

        $this->assertSame(0, Order::count());
        $this->assertSame(1, $product->fresh()->stock);
    }

    public function test_thank_you_page_is_not_shown_for_unpaid_online_orders(): void
    {
        $user = User::factory()->create();
        $order = $this->order($this->product(), 'razorpay', 1, ['user_id' => $user->id]);

        $this->actingAs($user)->get('/order/'.$order->number.'/thank-you')
            ->assertRedirect(route('account.orders.show', $order));
    }

    public function test_category_with_products_cannot_be_deleted(): void
    {
        $this->product();
        $admin = User::factory()->create(['is_admin' => true]);

        Livewire::actingAs($admin)
            ->test(EditCategory::class, ['record' => $this->category->getRouteKey()])
            ->callAction(DeleteAction::class);

        $this->assertModelExists($this->category);
        $this->assertSame(1, Product::count());

        $this->expectException(QueryException::class);
        $this->category->delete();
    }

    public function test_products_in_hidden_categories_are_hidden(): void
    {
        $product = $this->product();
        $this->category->update(['is_active' => false]);

        $this->get('/product/'.$product->slug)->assertNotFound();
        $this->assertSame(0, Product::active()->count());
    }

    public function test_replaced_and_deleted_product_photos_are_removed_from_storage(): void
    {
        Storage::fake('public');
        $first = UploadedFile::fake()->image('a.jpg')->store('products', 'public');
        $second = UploadedFile::fake()->image('b.jpg')->store('products', 'public');

        $product = $this->product();
        $product->update(['images' => [$first, $second]]);
        $product->update(['images' => [$second]]);

        Storage::disk('public')->assertMissing($first);
        Storage::disk('public')->assertExists($second);

        $product->delete();
        Storage::disk('public')->assertMissing($second);
    }
}
