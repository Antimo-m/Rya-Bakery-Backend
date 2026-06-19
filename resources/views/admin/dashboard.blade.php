<x-app-layout :title="$title">
    <x-slot name="header">
        <div>
            <span class="admin-kicker">Panoramica live</span>
            <h1>Dashboard</h1>
        </div>
    </x-slot>

    <section class="dashboard-hero">
        <div>
            <span class="live-pill"><i></i> Locale operativo</span>
            <h2>Ordini, prodotti e incasso sotto controllo.</h2>
            <p>Monitora il banco digitale di Rya Bakery con una vista pensata per servizio rapido e decisioni immediate.</p>
        </div>
        <div class="dashboard-hero__metric">
            <span>Incasso consegnato</span>
            <strong>€ {{ number_format($revenueTotal, 2, ',', '.') }}</strong>
        </div>
    </section>

    <section class="admin-grid stats-grid">
        <article class="admin-card"><span>Ordini attivi</span><strong>{{ $ordersTotal }}</strong><em>in gestione</em></article>
        <article class="admin-card"><span>Ricevuti</span><strong>{{ $ordersPending }}</strong><em>da accettare</em></article>
        <article class="admin-card"><span>In preparazione</span><strong>{{ $ordersAccepted }}</strong><em>al banco</em></article>
        <article class="admin-card"><span>Annullati</span><strong>{{ $ordersCancelled }}</strong><em>in storico</em></article>
        <article class="admin-card"><span>Prodotti totali</span><strong>{{ $productsTotal }}</strong><em>a catalogo</em></article>
        <article class="admin-card"><span>Disponibili</span><strong>{{ $productsAvailable }}</strong><em>ordinabili</em></article>
    </section>

    <section class="admin-section-title">
        <div>
            <span class="admin-kicker">Ultimi movimenti</span>
            <h2>Ordini recenti</h2>
        </div>
        <a class="admin-btn secondary admin-btn--icon" href="{{ route('admin.orders.index') }}" aria-label="Vedi ordini" title="Vedi ordini">
            <iconify-icon icon="solar:arrow-right-up-bold-duotone"></iconify-icon>
        </a>
    </section>

    <section class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Ultimi ordini</th>
                    <th>Tavolo</th>
                    <th>Totale</th>
                    <th>Stato</th>
                    <th>Ora</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($latestOrders as $order)
                    <tr>
                        <td>
                            <strong>{{ $order->customer_name }}</strong><br>
                            <x-admin.order-products :items="$order->items" />
                        </td>
                        <td>{{ $order->table_number }}</td>
                        <td>€ {{ number_format($order->total_price, 2, ',', '.') }}</td>
                        <td><span class="badge {{ $order->status }}">{{ $order->statusLabel() }}</span></td>
                        <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5">Il banco e tranquillo: nessun ordine in attesa.</td></tr>
                @endforelse
            </tbody>
        </table>
    </section>
</x-app-layout>
