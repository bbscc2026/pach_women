<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

#[Fillable([
    'number', 'user_id', 'status', 'payment_method', 'payment_status',
    'razorpay_order_id', 'razorpay_payment_id', 'subtotal', 'shipping', 'total',
    'name', 'email', 'phone', 'line1', 'line2', 'city', 'state', 'pincode', 'notes', 'tracking_number',
])]
class Order extends Model
{
    public const STATUSES = [
        'pending' => 'Pending',
        'confirmed' => 'Confirmed',
        'shipped' => 'Shipped',
        'delivered' => 'Delivered',
        'cancelled' => 'Cancelled',
    ];

    public const PAYMENT_STATUSES = [
        'pending' => 'Pending',
        'paid' => 'Paid',
        'failed' => 'Failed',
    ];

    public const PAYMENT_METHODS = [
        'cod' => 'Cash on Delivery',
        'razorpay' => 'Pay Online (UPI / Card / Netbanking)',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'shipping' => 'decimal:2',
            'total' => 'decimal:2',
            'stock_reserved_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            $order->number ??= 'PW'.now()->format('ymd').strtoupper(Str::random(5));
        });

        // Cancelling gives stock back; un-cancelling takes it again (only if it had been taken).
        static::updated(function (Order $order) {
            if (! $order->wasChanged('status')) {
                return;
            }

            if ($order->status === 'cancelled') {
                $order->restoreStock();
            } elseif ($order->getOriginal('status') === 'cancelled' && $order->shouldHoldStock()) {
                $order->reserveStock();
            }
        });
    }

    /**
     * COD orders hold stock from the moment they're placed; online orders once paid.
     */
    public function shouldHoldStock(): bool
    {
        return $this->payment_method === 'cod' || $this->payment_status === 'paid';
    }

    public function stockWasReserved(): bool
    {
        return $this->stock_reserved_at !== null;
    }

    /**
     * Take this order's quantities out of stock. Safe to call twice: it only acts once.
     * Product rows are locked so two orders can't take the same last piece.
     *
     * @return array<int, string> names of products that didn't have enough stock left
     */
    public function reserveStock(): array
    {
        if ($this->stockWasReserved()) {
            return [];
        }

        return DB::transaction(function () {
            $short = [];
            $products = Product::whereIn('id', $this->items()->pluck('product_id')->filter())
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($this->items()->get() as $item) {
                $product = $products->get($item->product_id);

                if (! $product) {
                    continue;
                }

                if ($product->stock < $item->quantity) {
                    $short[] = $product->name;
                }

                $product->decrement('stock', min($item->quantity, $product->stock));
            }

            $this->forceFill(['stock_reserved_at' => now()])->saveQuietly();

            return $short;
        });
    }

    /**
     * Put this order's quantities back. Safe to call twice: it only acts once.
     */
    public function restoreStock(): void
    {
        if (! $this->stockWasReserved()) {
            return;
        }

        DB::transaction(function () {
            foreach ($this->items()->with('product')->get() as $item) {
                $item->product?->increment('stock', $item->quantity);
            }

            $this->forceFill(['stock_reserved_at' => null])->saveQuietly();
        });
    }

    /**
     * Record a successful Razorpay payment. Called from the checkout redirect and the webhook,
     * whichever arrives first; the second call does nothing.
     */
    public function markPaid(string $paymentId): void
    {
        if ($this->payment_status === 'paid') {
            return;
        }

        $this->update([
            'payment_status' => 'paid',
            'status' => $this->status === 'pending' ? 'confirmed' : $this->status,
            'razorpay_payment_id' => $paymentId,
        ]);

        // Paid after the order was cancelled (e.g. abandoned, then paid later): leave stock alone, refund manually.
        if ($this->status === 'cancelled') {
            Log::warning('Payment received for a cancelled order — refund or reinstate it', ['order' => $this->number]);

            return;
        }

        $short = $this->reserveStock();

        if ($short !== []) {
            Log::warning('Order paid but stock ran out', ['order' => $this->number, 'products' => $short]);
        }
    }

    /**
     * Unpaid online orders read as "Awaiting payment" to the customer rather than "Pending".
     */
    public function isAwaitingPayment(): bool
    {
        return $this->payment_method === 'razorpay' && $this->payment_status !== 'paid' && $this->status === 'pending';
    }

    public function paymentLabel(): string
    {
        return match (true) {
            $this->payment_status === 'paid' => 'Paid',
            $this->payment_method === 'cod' => 'Pay on delivery',
            $this->payment_status === 'failed' => 'Payment failed',
            default => 'Payment not completed',
        };
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getRouteKeyName(): string
    {
        return 'number';
    }

    public function statusLabel(): string
    {
        if ($this->isAwaitingPayment()) {
            return 'Awaiting payment';
        }

        return self::STATUSES[$this->status] ?? ucfirst($this->status);
    }

    public function paymentMethodLabel(): string
    {
        return self::PAYMENT_METHODS[$this->payment_method] ?? $this->payment_method;
    }

    public function address(): string
    {
        return collect([$this->line1, $this->line2, $this->city, $this->state, $this->pincode])
            ->filter()
            ->implode(', ');
    }
}
