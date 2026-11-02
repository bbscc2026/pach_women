<span style="display:flex;align-items:center;gap:10px;min-width:0;white-space:normal">
    @if ($product->thumbnailUrl())
        <img src="{{ $product->thumbnailUrl() }}" alt="" loading="lazy" style="width:42px;height:52px;object-fit:cover;border-radius:6px;flex-shrink:0;background:#e9e1d6">
    @else
        <span aria-hidden="true" style="display:flex;width:42px;height:52px;align-items:center;justify-content:center;border-radius:6px;flex-shrink:0;background:#e9e1d6;color:#444;font-size:10px">PHOTO</span>
    @endif
    <span style="min-width:0;overflow-wrap:anywhere">
        <span style="display:block;font-weight:600">{{ $product->name }}</span>
        <span style="display:block;font-size:12px;opacity:.8">{{ inr($product->finalPrice()) }} · {{ $product->stock > 0 ? $product->stock.' left' : 'Out of stock' }}@if ($product->sku) · {{ $product->sku }}@endif</span>
    </span>
</span>
