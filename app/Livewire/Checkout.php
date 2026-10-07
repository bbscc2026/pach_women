<?php

namespace App\Livewire;

use App\Models\Order;
use App\Models\Product;
use App\Support\Cart;
use App\Support\Razorpay;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Throwable;

#[Title('Checkout')]
class Checkout extends Component
{
    public string $name = '';

    public string $email = '';

    public string $phone = '';

    public string $line1 = '';

    public string $line2 = '';

    public string $city = '';

    public string $state = 'Kerala';

    public string $pincode = '';

    public string $notes = '';

    public string $paymentMethod = '';

    public bool $saveAddress = true;

    #[Url(as: 'payment')]
    public string $paymentNotice = '';

    public function mount(Cart $cart)
    {
        if ($cart->items()->isEmpty()) {
            return $this->redirectRoute('cart', navigate: true);
        }

        $user = auth()->user();
        $address = $user->addresses()->orderByDesc('is_default')->latest()->first();

        $this->name = $address->name ?? $user->name;
        $this->email = $user->email;
        $this->phone = $address->phone ?? ($user->phone ?? '');

        if ($address) {
            $this->fill($address->only('line1', 'city', 'state', 'pincode'));
            $this->line2 = $address->line2 ?? '';
            $this->saveAddress = false;
        }

        $this->paymentMethod = array_key_first($this->paymentMethods());
    }

    /**
     * @return array<string, string>
     */
    public function paymentMethods(): array
    {
        return collect(Order::PAYMENT_METHODS)
            ->filter(fn ($label, $key) => match ($key) {
                'razorpay' => Razorpay::enabled(),
                'cod' => config('shop.cod_enabled'),
                default => false,
            })
            ->all();
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150'],
            'phone' => ['required', 'regex:/^(\+91[\s-]?)?[6-9]\d{9}$/'],
            'line1' => ['required', 'string', 'max:200'],
            'line2' => ['nullable', 'string', 'max:200'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['required', Rule::in(config('shop.states'))],
            'pincode' => ['required', 'digits:6'],
            'notes' => ['nullable', 'string', 'max:500'],
            'paymentMethod' => ['required', Rule::in(array_keys($this->paymentMethods()))],
        ];
    }

    protected function messages(): array
    {
        return [
            'phone.regex' => 'Enter a valid 10-digit Indian mobile number.',
            'pincode.digits' => 'PIN code must be 6 digits.',
        ];
    }

    public function placeOrder()
    {
        $data = $this->validate();
        $cart = app(Cart::class);
        $items = $cart->items();

        if ($items->isEmpty()) {
            return $this->redirectRoute('cart', navigate: true);
        }

        $subtotal = $cart->subtotal($items);
        $shipping = $cart->shipping($subtotal);

        $order = DB::transaction(function () use ($data, $items, $subtotal, $shipping) {
            // Lock the products and re-check stock so two shoppers can't both buy the last piece.
            $stock = Product::whereIn('id', $items->pluck('product.id'))->lockForUpdate()->pluck('stock', 'id');

            foreach ($items as $item) {
                $left = (int) ($stock[$item->product->id] ?? 0);

                if ($item->requested > $left) {
                    $this->addError('cart', $left > 0
                        ? "Only {$left} left of {$item->product->name}. Please update your cart."
                        : "{$item->product->name} just sold out. Please remove it from your cart.");

                    return null;
                }
            }

            $order = Order::create([
                'user_id' => auth()->id(),
                'payment_method' => $data['paymentMethod'],
                'status' => 'pending',
                'payment_status' => 'pending',
                'subtotal' => $subtotal,
                'shipping' => $shipping,
                'total' => $subtotal + $shipping,
                ...collect($data)->only('name', 'email', 'phone', 'line1', 'line2', 'city', 'state', 'pincode', 'notes')->all(),
            ]);

            foreach ($items as $item) {
                $order->items()->create([
                    'product_id' => $item->product->id,
                    'name' => $item->product->name,
                    'size' => $item->size,
                    'price' => $item->price,
                    'quantity' => $item->quantity,
                ]);
            }

            if ($this->saveAddress) {
                $user = auth()->user();
                $user->addresses()->create([
                    ...collect($data)->only('name', 'phone', 'line1', 'line2', 'city', 'state', 'pincode')->all(),
                    'is_default' => ! $user->addresses()->exists(),
                ]);
            }

            if (! auth()->user()->phone) {
                auth()->user()->update(['phone' => $data['phone']]);
            }

            // COD takes stock now, inside the same locked transaction. Online orders take it once paid.
            if ($order->payment_method === 'cod') {
                $order->update(['status' => 'confirmed']);
                $order->reserveStock();
            }

            return $order;
        });

        if (! $order) {
            return;
        }

        if ($order->payment_method === 'cod') {
            $cart->clear();
            $this->dispatch('cart-updated');

            return $this->redirectRoute('order.placed', $order);
        }

        try {
            $order->update(['razorpay_order_id' => app(Razorpay::class)->createOrder($order)]);
        } catch (Throwable $e) {
            Log::error('Razorpay order creation failed', ['order' => $order->number, 'error' => $e->getMessage()]);
            $order->update(['payment_status' => 'failed', 'status' => 'cancelled']);
            $this->addError('cart', 'Online payment is unavailable right now. Please try again or choose Cash on Delivery.');

            return;
        }

        $this->dispatch('razorpay-open', [
            'key' => config('shop.razorpay.key'),
            'amount' => (int) round($order->total * 100),
            'name' => config('shop.name'),
            'order_number' => $order->number,
            'razorpay_order_id' => $order->razorpay_order_id,
            'prefill' => ['name' => $order->name, 'email' => $order->email, 'contact' => $order->phone],
            'verify_url' => route('payment.razorpay.verify'),
            'cancel_url' => route('checkout', ['payment' => 'cancelled']),
            'csrf' => csrf_token(),
        ]);
    }

    public function render(Cart $cart)
    {
        $items = $cart->items();
        $subtotal = $cart->subtotal($items);
        $shipping = $cart->shipping($subtotal);

        return view('livewire.checkout', [
            'items' => $items,
            'subtotal' => $subtotal,
            'shipping' => $shipping,
            'total' => $subtotal + $shipping,
            'methods' => $this->paymentMethods(),
            'states' => config('shop.states'),
        ]);
    }
}
