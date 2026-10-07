<?php

use App\Http\Controllers\RazorpayController;
use App\Livewire\Account;
use App\Livewire\Auth;
use App\Livewire\CartPage;
use App\Livewire\Checkout;
use App\Livewire\Home;
use App\Livewire\ProductShow;
use App\Livewire\Shop;
use App\Models\ContentPage;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Storefront
Route::get('/', Home::class)->name('home');
Route::get('/shop', Shop::class)->name('shop');
Route::get('/category/{category}', Shop::class)->name('category');
Route::get('/product/{product}', ProductShow::class)->name('product');
Route::get('/cart', CartPage::class)->name('cart');

// Called by Razorpay's servers (no session, no CSRF token; authenticated by signature).
Route::post('/payment/razorpay/webhook', [RazorpayController::class, 'webhook'])
    ->middleware('throttle:60,1')
    ->name('payment.razorpay.webhook');

Route::get('/pages/{page}', function (ContentPage $page) {
    abort_unless($page->is_active, 404);

    return view('page', ['page' => $page]);
})->name('page');

// Guest-only auth pages
Route::middleware('guest')->group(function () {
    Route::get('/login', Auth\Login::class)->name('login');
    Route::get('/register', Auth\Register::class)->name('register');
    Route::get('/forgot-password', Auth\ForgotPassword::class)->name('password.request');
    Route::get('/reset-password/{token}', Auth\ResetPassword::class)->name('password.reset');
});

Route::middleware('auth')->group(function () {
    Route::get('/checkout', Checkout::class)->name('checkout');
    Route::post('/payment/razorpay/verify', [RazorpayController::class, 'verify'])->name('payment.razorpay.verify');

    Route::get('/order/{order}/thank-you', function (Order $order) {
        abort_unless($order->user_id === auth()->id(), 404);

        if ($order->payment_method === 'razorpay' && $order->payment_status !== 'paid') {
            return redirect()->route('account.orders.show', $order)
                ->with('error', 'Payment for this order was not completed.');
        }

        return view('order-placed', ['order' => $order->load('items')]);
    })->name('order.placed');

    Route::prefix('account')->name('account.')->group(function () {
        Route::redirect('/', '/account/orders');
        Route::get('/orders', Account\Orders::class)->name('orders');
        Route::get('/orders/{order}', Account\OrderShow::class)->name('orders.show');
        Route::get('/addresses', Account\Addresses::class)->name('addresses');
        Route::get('/profile', Account\Profile::class)->name('profile');
    });

    Route::post('/logout', function (Request $request) {
        auth()->guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    })->name('logout');
});
