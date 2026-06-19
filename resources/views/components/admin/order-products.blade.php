@props(['items'])

@php
    $orderItems = collect($items);
    $hasManyProducts = $orderItems->count() > 4;
@endphp

<div class="admin-product-stack" data-order-products>
    @foreach ($hasManyProducts ? $orderItems->take(1) : $orderItems as $item)
        <span class="admin-product-chip">
            <img src="{{ $item->product?->image_url }}" alt="">
            <span>{{ $item->quantity }}x {{ $item->product?->name ?? 'Prodotto' }}</span>
        </span>
    @endforeach

    @if ($hasManyProducts)
        <div class="admin-product-stack__extra" data-order-products-extra hidden>
            @foreach ($orderItems->skip(1) as $item)
                <span class="admin-product-chip">
                    <img src="{{ $item->product?->image_url }}" alt="">
                    <span>{{ $item->quantity }}x {{ $item->product?->name ?? 'Prodotto' }}</span>
                </span>
            @endforeach
        </div>

        <button class="admin-products-toggle" type="button" data-order-products-toggle aria-expanded="false">
            <iconify-icon icon="solar:menu-dots-bold"></iconify-icon>
            <span data-toggle-label>+{{ $orderItems->count() - 1 }} altri prodotti</span>
        </button>
    @endif
</div>
