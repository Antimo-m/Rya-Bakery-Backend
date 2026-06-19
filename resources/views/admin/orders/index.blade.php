<x-app-layout :title="$title">
    <x-slot name="header">
        <div>
            <span class="admin-kicker">Servizio tavoli</span>
            <h1>Ordini</h1>
        </div>
    </x-slot>

    <section class="admin-table-wrap">
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
            <tbody>
                @forelse ($orders as $order)
                    <tr>
                        <td>
                            <strong>{{ $order->customer_name }}</strong><br>
                            <small>Tavolo {{ $order->table_number }} · {{ $order->slug }}</small>
                        </td>
                        <td>
                            <div class="admin-product-stack">
                            @foreach ($order->items as $item)
                                <span class="admin-product-chip">
                                    <img src="{{ $item->product->image_url }}" alt="">
                                    <span>{{ $item->quantity }}x {{ $item->product->name }}</span>
                                </span>
                            @endforeach
                            </div>
                        </td>
                        <td>€ {{ number_format($order->total_price, 2, ',', '.') }}</td>
                        <td><span class="badge {{ $order->status }}">{{ $order->statusLabel() }}</span></td>
                        <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            <div class="admin-actions">
                                @if ($order->status === \App\Models\Order::STATUS_RECEIVED)
                                    <form method="POST" action="{{ route('admin.orders.accept', $order) }}" data-confirm="Accettare questo ordine e metterlo in lavorazione?">
                                        @csrf
                                        @method('PATCH')
                                        <button class="admin-btn success admin-btn--icon" type="submit" aria-label="Accetta ordine" title="Accetta">
                                            <iconify-icon icon="solar:check-circle-bold-duotone"></iconify-icon>
                                        </button>
                                    </form>
                                @endif

                                @if ($order->status === \App\Models\Order::STATUS_PENDING)
                                    <form method="POST" action="{{ route('admin.orders.complete', $order) }}" data-confirm="Confermare la consegna di questo ordine?">
                                        @csrf
                                        @method('PATCH')
                                        <button class="admin-btn success admin-btn--icon" type="submit" aria-label="Completa ordine" title="Completa">
                                            <iconify-icon icon="solar:check-read-bold-duotone"></iconify-icon>
                                        </button>
                                    </form>
                                @endif

                                @if (in_array($order->status, [\App\Models\Order::STATUS_RECEIVED, \App\Models\Order::STATUS_PENDING], true))
                                    <form method="POST" action="{{ route('admin.orders.cancel', $order) }}" data-confirm="Annullare o rifiutare questo ordine? Sara spostato nello storico.">
                                        @csrf
                                        @method('PATCH')
                                        <button class="admin-btn danger admin-btn--icon" type="submit" aria-label="Annulla ordine" title="Annulla">
                                            <iconify-icon icon="solar:trash-bin-trash-bold-duotone"></iconify-icon>
                                        </button>
                                    </form>
                                @endif

                                <a class="admin-btn edit admin-btn--icon" href="{{ route('admin.orders.edit', $order) }}" aria-label="Modifica ordine" title="Modifica">
                                    <iconify-icon icon="solar:pen-new-square-bold-duotone"></iconify-icon>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6">Nessun ordine ricevuto.</td></tr>
                @endforelse
            </tbody>
        </table>
    </section>

    <div style="margin-top: 18px">{{ $orders->links() }}</div>
</x-app-layout>
