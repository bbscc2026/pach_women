@props(['subtotal', 'shipping', 'total'])

<dl class="space-y-3 text-sm">
    <div class="flex justify-between">
        <dt class="text-neutral-600">Subtotal</dt>
        <dd>{{ inr($subtotal) }}</dd>
    </div>
    <div class="flex justify-between">
        <dt class="text-neutral-600">Shipping</dt>
        <dd>{{ $shipping > 0 ? inr($shipping) : 'Free' }}</dd>
    </div>
    <div class="flex justify-between border-t border-sand pt-3 text-base font-medium">
        <dt>Total</dt>
        <dd>{{ inr($total) }}</dd>
    </div>
</dl>
