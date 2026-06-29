@props(['items', 'compact' => false])

@php
    $orderItems = collect($items);
    $visibleItems = $compact ? $orderItems->take(2) : $orderItems;
@endphp

<ul {{ $attributes->class(['admin-order-items', 'is-compact' => $compact]) }}>
    @foreach ($visibleItems as $item)
        <li>
            <img src="{{ $item->product?->image_url }}" alt="">
            <span>
                <strong>{{ $item->quantity }}× {{ $item->product?->name ?? 'Prodotto' }}</strong>
                @unless ($compact)
                    <small>€ {{ number_format($item->line_total, 2, ',', '.') }}</small>
                @endunless
            </span>
        </li>
    @endforeach
    @if ($compact && $orderItems->count() > $visibleItems->count())
        <li class="admin-order-items__more">
            +{{ $orderItems->count() - $visibleItems->count() }} altri prodotti
        </li>
    @endif
</ul>
