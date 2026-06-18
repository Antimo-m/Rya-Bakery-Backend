<x-app-layout :title="$title">
    <x-slot name="header">
        <div>
            <span class="admin-kicker">Incassi</span>
            <h1>Analisi</h1>
        </div>
    </x-slot>

    <form class="admin-filters" method="GET" action="{{ route('admin.analysis.index') }}">
        <label>
            Giorno
            <input name="day" type="date" value="{{ $filters['day'] }}">
        </label>
        <label>
            Mese
            <input name="month" type="month" value="{{ $filters['month'] }}">
        </label>
        <button class="admin-btn" type="submit">Aggiorna</button>
        <a class="admin-btn secondary" href="{{ route('admin.analysis.index') }}">Oggi</a>
    </form>

    <section class="admin-grid stats-grid">
        <article class="admin-card"><span>Incasso oggi</span><strong>€ {{ number_format($todayTotal, 2, ',', '.') }}</strong><em>ordini completati</em></article>
        <article class="admin-card"><span>Giorno selezionato</span><strong>€ {{ number_format($dayTotal, 2, ',', '.') }}</strong><em>{{ \Illuminate\Support\Carbon::parse($filters['day'])->format('d/m/Y') }}</em></article>
        <article class="admin-card"><span>Mese selezionato</span><strong>€ {{ number_format($monthTotal, 2, ',', '.') }}</strong><em>{{ $monthOrdersCount }} ordini completati</em></article>
    </section>

    <section class="admin-section-title">
        <div>
            <span class="admin-kicker">Dettaglio</span>
            <h2>Ordini del giorno selezionato</h2>
        </div>
    </section>

    <section class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Ordine</th>
                    <th>Prodotti</th>
                    <th>Totale</th>
                    <th>Completato</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($dayOrders as $order)
                    <tr>
                        <td>
                            <strong>{{ $order->customer_name }}</strong><br>
                            <small>Tavolo {{ $order->table_number }} · {{ $order->slug }}</small>
                        </td>
                        <td>{{ $order->items->pluck('product.name')->join(', ') }}</td>
                        <td>€ {{ number_format($order->total_price, 2, ',', '.') }}</td>
                        <td>{{ $order->delivered_at?->format('d/m/Y H:i') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4">Nessun ordine completato nel giorno selezionato.</td></tr>
                @endforelse
            </tbody>
        </table>
    </section>
</x-app-layout>
