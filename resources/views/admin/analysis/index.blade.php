<x-app-layout :title="$title">
    <x-slot name="header">
        <div>
            <span class="admin-kicker">Incassi</span>
            <h1>Analisi</h1>
        </div>
    </x-slot>

    <section class="admin-grid stats-grid">
        <article class="admin-card"><span>Incasso Giornaliero</span><strong>€ {{ number_format($todayTotal, 2, ',', '.') }}</strong><em>ordini completati</em></article>
        <article class="admin-card"><span>Giorno selezionato</span><strong>€ {{ number_format($dayTotal, 2, ',', '.') }}</strong><em>{{ \Illuminate\Support\Carbon::parse($filters['day'])->format('d/m/Y') }}</em></article>
        <article class="admin-card"><span>Mese selezionato</span><strong>€ {{ number_format($monthTotal, 2, ',', '.') }}</strong><em>{{ $monthOrdersCount }} ordini completati</em></article>
    </section>

    <section class="admin-section-title">
        <div>
            <span class="admin-kicker">Dettaglio</span>
            <h2>Ordini del giorno selezionato</h2>
        </div>
        <form class="admin-filters admin-filters--compact admin-filters--inline admin-filters--analysis" method="GET" action="{{ route('admin.analysis.index') }}">
            <label>
                Giorno
                <span class="custom-date-field">
                    <iconify-icon icon="solar:calendar-date-bold-duotone"></iconify-icon>
                    <input name="day" type="text" inputmode="numeric" pattern="\d{4}-\d{2}-\d{2}" placeholder="YYYY-MM-DD" value="{{ $filters['day'] }}">
                </span>
            </label>
            <button class="admin-btn admin-btn--icon" type="submit" aria-label="Filtra analisi" title="Filtra">
                <iconify-icon icon="solar:filter-bold-duotone"></iconify-icon>
            </button>
            <a class="admin-btn secondary admin-btn--icon" href="{{ route('admin.analysis.index') }}" aria-label="Reset filtri analisi" title="Reset">
                <iconify-icon icon="solar:restart-circle-bold-duotone"></iconify-icon>
            </a>
        </form>
    </section>

    <section class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Ordine</th>
                    <th>Tavolo</th>
                    <th>Prodotti</th>
                    <th>Totale</th>
                    <th>Completato</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($dayOrders as $order)
                    <tr>
                        <td>
                            <strong>{{ $order->customer_name }}</strong>
                        </td>
                        <td><small>{{ $order->slug }}</small></td>
                        <td>{{ $order->table_number }}</td>
                        <td>
                            <x-admin.order-products :items="$order->items" />
                        </td>
                        <td>€ {{ number_format($order->total_price, 2, ',', '.') }}</td>
                        <td>{{ $order->delivered_at?->format('d/m/Y H:i') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6">Nessun ordine completato nel giorno selezionato.</td></tr>
                @endforelse
            </tbody>
        </table>
    </section>
</x-app-layout>
