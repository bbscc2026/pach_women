<?php

namespace Tests\Feature;

use App\Filament\Resources\Orders\Pages\EditOrder;
use App\Filament\Resources\Orders\Pages\ListOrders;
use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    private function codOrder(Product $product, int $quantity = 2): Order
    {
        $order = Order::create([
            'payment_method' => 'cod', 'status' => 'confirmed', 'payment_status' => 'pending',
            'subtotal' => 2000, 'shipping' => 0, 'total' => 2000,
            'name' => 'Asha', 'email' => 'asha@example.com', 'phone' => '9876543210',
            'line1' => '12 MG Road', 'city' => 'Kozhikode', 'state' => 'Kerala', 'pincode' => '673001',
        ]);
        $order->items()->create(['product_id' => $product->id, 'name' => $product->name, 'size' => 'M', 'price' => 1000, 'quantity' => $quantity]);
        $order->reserveStock();

        return $order;
    }

    public function test_saving_an_order_in_admin_keeps_its_items_and_cancelling_restocks(): void
    {
        $category = Category::create(['name' => 'Kurtis', 'slug' => 'kurtis']);
        $product = $category->products()->create(['name' => 'Kurti', 'slug' => 'kurti', 'price' => 1000, 'stock' => 5]);
        $order = $this->codOrder($product);
        $this->assertSame(3, $product->fresh()->stock);

        Livewire::actingAs($this->admin())
            ->test(EditOrder::class, ['record' => $order->getRouteKey()])
            ->fillForm(['status' => 'shipped', 'tracking_number' => 'TRK1'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame(1, $order->items()->count());
        $this->assertSame('TRK1', $order->fresh()->tracking_number);
        $this->assertSame(3, $product->fresh()->stock);

        Livewire::actingAs($this->admin())
            ->test(EditOrder::class, ['record' => $order->getRouteKey()])
            ->fillForm(['status' => 'cancelled'])
            ->call('save');

        $this->assertSame(5, $product->fresh()->stock);
    }

    public function test_quick_actions_ship_deliver_and_cancel_orders(): void
    {
        $category = Category::create(['name' => 'Kurtis', 'slug' => 'kurtis']);
        $product = $category->products()->create(['name' => 'Kurti', 'slug' => 'kurti', 'price' => 1000, 'stock' => 5]);
        $order = $this->codOrder($product);
        $order->update(['status' => 'confirmed']);
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(ListOrders::class)
            ->assertCanSeeTableRecords([$order])
            ->callTableAction('ship', $order, data: ['tracking_number' => 'DTDC123'])
            ->assertHasNoTableActionErrors();

        $order->refresh();
        $this->assertSame('shipped', $order->status);
        $this->assertSame('DTDC123', $order->tracking_number);

        Livewire::actingAs($admin)
            ->test(EditOrder::class, ['record' => $order->getRouteKey()])
            ->callAction('deliver');

        $order->refresh();
        $this->assertSame('delivered', $order->status);
        $this->assertSame('paid', $order->payment_status, 'COD is paid once delivered.');

        $second = $this->codOrder($product, 1);
        $second->update(['status' => 'confirmed']);
        $this->assertSame(2, $product->fresh()->stock);

        Livewire::actingAs($admin)
            ->test(ListOrders::class)
            ->callTableAction('cancel', $second);

        $this->assertSame('cancelled', $second->fresh()->status);
        $this->assertSame(3, $product->fresh()->stock);
    }

    public function test_admin_pages_load_and_include_home_screen_manifest(): void
    {
        $admin = $this->admin();

        foreach (['/admin', '/admin/orders', '/admin/products', '/admin/products/create', '/admin/categories', '/admin/banners', '/admin/users', '/admin/content-pages', '/admin/site-settings'] as $url) {
            $this->assertSame(200, $this->actingAs($admin)->get($url)->status(), "Admin page {$url} failed to load.");
        }

        $this->actingAs($admin)->get('/admin/orders')
            ->assertSee('admin.webmanifest', false)
            ->assertSee('apple-touch-icon', false);
    }

    public function test_products_can_be_created_without_a_sku(): void
    {
        $category = Category::create(['name' => 'Kurtis', 'slug' => 'kurtis']);
        $admin = $this->admin();

        foreach (['First kurti', 'Second kurti'] as $name) {
            Livewire::actingAs($admin)
                ->test(CreateProduct::class)
                ->fillForm([
                    'name' => $name,
                    'slug' => str($name)->slug()->toString(),
                    'category_id' => $category->id,
                    'price' => 999,
                    'stock' => 3,
                    'sizes' => ['M'],
                ])
                ->call('create')
                ->assertHasNoFormErrors();
        }

        $this->assertSame(2, Product::count());
    }
}
