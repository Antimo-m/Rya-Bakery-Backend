<x-app-layout :title="$title">
    <x-slot name="header">
        <div>
            <span class="admin-kicker">Servizio tavoli</span>
            <h1>Ordini</h1>
        </div>
        <button class="admin-btn secondary admin-sound-toggle" type="button" data-order-sound-toggle aria-pressed="false">
            <iconify-icon icon="solar:bell-off-linear" data-sound-icon></iconify-icon>
            <span data-sound-label>Audio spento</span>
        </button>
    </x-slot>

    <section
        class="admin-table-wrap"
        data-realtime-orders
        data-accept-url-template="{{ route('admin.orders.accept', ['order' => '__SLUG__']) }}"
        data-cancel-url-template="{{ route('admin.orders.cancel', ['order' => '__SLUG__']) }}"
        data-edit-url-template="{{ route('admin.orders.edit', ['order' => '__SLUG__']) }}"
        data-live-url="{{ route('admin.orders.live') }}"
    >
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Ordine</th>
                    <th>Prodotti</th>
                    <th>Totale</th>
                    <th>Stato</th>
                    <th>Data</th>
                    <th>Azioni</th>
                </tr>
            </thead>
            <tbody data-orders-table-body>
                @forelse ($orders as $order)
                    <tr data-order-id="{{ $order->id }}">
                        <td>
                            <strong>{{ $order->customer_name }}</strong><br>
                            <small>Tavolo {{ $order->table_number }} · {{ $order->slug }}</small>
                        </td>
                        <td>
                            <div class="admin-product-stack" data-order-products>
                                @php
                                    $orderItems = $order->items;
                                    $hasManyProducts = $orderItems->count() > 4;
                                @endphp

                                @foreach ($hasManyProducts ? $orderItems->take(1) : $orderItems as $item)
                                    <span class="admin-product-chip">
                                        <img src="{{ $item->product->image_url }}" alt="">
                                        <span>{{ $item->quantity }}x {{ $item->product->name }}</span>
                                    </span>
                                @endforeach

                                @if ($hasManyProducts)
                                    <div class="admin-product-stack__extra" data-order-products-extra hidden>
                                        @foreach ($orderItems->skip(1) as $item)
                                            <span class="admin-product-chip">
                                                <img src="{{ $item->product->image_url }}" alt="">
                                                <span>{{ $item->quantity }}x {{ $item->product->name }}</span>
                                            </span>
                                        @endforeach
                                    </div>

                                    <button class="admin-products-toggle" type="button" data-order-products-toggle aria-expanded="false">
                                        <iconify-icon icon="solar:menu-dots-bold"></iconify-icon>
                                        <span data-toggle-label>+{{ $orderItems->count() - 1 }} altri prodotti</span>
                                    </button>
                                @endif
                            </div>
                        </td>
                        <td>€ {{ number_format($order->total_price, 2, ',', '.') }}</td>
                        <td><span class="badge {{ $order->status }}">{{ $order->statusLabel() }}</span></td>
                        <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            <div class="admin-actions">
                                @if ($order->status === \App\Models\Order::STATUS_RECEIVED)
                                    <form method="POST" action="{{ route('admin.orders.accept', $order) }}" data-confirm="Prendere questo ordine in preparazione?">
                                        @csrf
                                        @method('PATCH')
                                        <button class="admin-btn success admin-btn--icon" type="submit" aria-label="Accetta ordine" title="Accetta">
                                            <iconify-icon icon="solar:check-square-bold-duotone"></iconify-icon>
                                        </button>
                                    </form>
                                @endif

                                @if ($order->status === \App\Models\Order::STATUS_PENDING)
                                    <form method="POST" action="{{ route('admin.orders.complete', $order) }}" data-confirm="Confermare la consegna di questo ordine?">
                                        @csrf
                                        @method('PATCH')
                                        <button class="admin-btn success admin-btn--icon" type="submit" aria-label="Completa ordine" title="Completa">
                                            <iconify-icon icon="solar:like-bold-duotone"></iconify-icon>
                                        </button>
                                    </form>
                                @endif

                                @if (in_array($order->status, [\App\Models\Order::STATUS_RECEIVED, \App\Models\Order::STATUS_PENDING], true))
                                    <form method="POST" action="{{ route('admin.orders.cancel', $order) }}" data-confirm="Annullare o rifiutare questo ordine? Sara spostato nello storico.">
                                        @csrf
                                        @method('PATCH')
                                        <button class="admin-btn danger admin-btn--icon" type="submit" aria-label="Annulla ordine" title="Annulla">
                                            <iconify-icon icon="solar:close-square-bold-duotone"></iconify-icon>
                                        </button>
                                    </form>
                                @endif

                                <a class="admin-btn edit admin-btn--icon" href="{{ route('admin.orders.edit', $order) }}" aria-label="Modifica ordine" title="Modifica">
                                    <iconify-icon icon="solar:pen-2-bold-duotone"></iconify-icon>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr data-orders-empty-row><td colspan="6">Nessun ordine in attesa: il banco e libero.</td></tr>
                @endforelse
            </tbody>
        </table>
    </section>

    <div style="margin-top: 18px">{{ $orders->links() }}</div>
</x-app-layout>
