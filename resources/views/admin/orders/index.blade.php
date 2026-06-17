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
                            @foreach ($order->items as $item)
                                {{ $item->quantity }}x {{ $item->product->name }}<br>
                            @endforeach
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
                                        <button class="admin-btn success" type="submit">Accetta</button>
                                    </form>
                                @endif

                                @if (in_array($order->status, [\App\Models\Order::STATUS_RECEIVED, \App\Models\Order::STATUS_PENDING], true))
                                    <form method="POST" action="{{ route('admin.orders.complete', $order) }}" data-confirm="Confermare la consegna di questo ordine?">
                                        @csrf
                                        @method('PATCH')
                                        <button class="admin-btn success" type="submit" title="Completa ordine">✓ Completa</button>
                                    </form>
                                @endif

                                @if ($order->status !== \App\Models\Order::STATUS_DELIVERED)
                                    <form method="POST" action="{{ route('admin.orders.cancel', $order) }}" data-confirm="Annullare o rifiutare questo ordine? Sara spostato nello storico.">
                                        @csrf
                                        @method('PATCH')
                                        <button class="admin-btn danger" type="submit">Annulla</button>
                                    </form>
                                @endif

                                <a class="admin-btn secondary" href="{{ route('admin.orders.edit', $order) }}">Modifica</a>
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
