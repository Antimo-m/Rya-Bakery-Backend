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
            <span>Incasso ordini pronti</span>
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

    <section class="admin-ops-grid admin-ops-grid--single" id="checklist-banco">
        <article class="admin-panel admin-panel--checklist">
            <div class="admin-panel__heading">
                <div>
                    <span class="admin-kicker">Operatività</span>
                    <h2>Checklist banco</h2>
                    <p>{{ $checklistDate->isToday() ? 'Attività di oggi' : 'Attività del '.$checklistDate->format('d/m/Y') }}</p>
                </div>
                <form class="admin-date-filter" method="GET" action="{{ route('admin.dashboard') }}">
                    <label>
                        Giorno
                        <span class="custom-date-field">
                            <i class="bi bi-calendar3" aria-hidden="true"></i>
                            <input name="checklist_date" type="text" value="{{ $checklistDate->format('Y-m-d') }}" readonly inputmode="none" data-submit-on-select>
                        </span>
                    </label>
                </form>
            </div>
            <form class="admin-checklist-form" method="POST" action="{{ route('admin.checklist.store') }}">
                @csrf
                <input type="hidden" name="checklist_date" value="{{ $checklistDate->format('Y-m-d') }}">
                <label>
                    Nuova checklist
                    <input name="title" required maxlength="160" placeholder="Es. Verificare banco prima del servizio">
                </label>
                <button class="admin-btn" type="submit" aria-label="Aggiungi checklist">
                    <i class="bi bi-plus-lg" aria-hidden="true"></i>
                    <span>Aggiungi</span>
                </button>
            </form>
            <ul class="admin-checklist">
                @forelse ($dashboardChecklist as $item)
                    <li class="{{ $item->is_done ? 'is-done' : 'is-pending' }}">
                        <form class="admin-checklist-row" method="POST" action="{{ route('admin.checklist.update', $item) }}" data-checklist-row>
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="checklist_date" value="{{ $checklistDate->format('Y-m-d') }}">
                            <label class="admin-checklist-status">
                                <input type="checkbox" name="is_done" value="1" @checked($item->is_done) data-checklist-status-input>
                                <i class="bi {{ $item->is_done ? 'bi-check-circle-fill' : 'bi-clock' }}" aria-hidden="true"></i>
                            </label>
                            <input name="title" value="{{ $item->title }}" required maxlength="160" readonly data-checklist-title>
                            <button class="admin-btn edit admin-btn--icon" type="button" aria-label="Modifica checklist" title="Modifica" data-checklist-edit>
                                <i class="bi bi-pencil" aria-hidden="true"></i>
                            </button>
                            <button class="admin-btn success admin-checklist-confirm" type="submit" aria-label="Conferma modifica checklist" title="Conferma" data-checklist-save hidden>
                                <i class="bi bi-check-lg" aria-hidden="true"></i>
                            </button>
                        </form>
                        <form method="POST" action="{{ route('admin.checklist.destroy', $item) }}" data-confirm="Eliminare questa voce checklist?">
                            @csrf
                            @method('DELETE')
                            <button class="admin-btn danger admin-btn--icon" type="submit" aria-label="Elimina checklist" title="Elimina">
                                <i class="bi bi-trash" aria-hidden="true"></i>
                            </button>
                        </form>
                    </li>
                @empty
                    <li class="is-empty">
                        <i class="bi bi-clipboard2-check" aria-hidden="true"></i>
                        <span>Nessuna attività pianificata per questo giorno.</span>
                    </li>
                @endforelse
            </ul>
        </article>
    </section>

    <section class="admin-section-title">
        <div>
            <span class="admin-kicker">Ultimi movimenti</span>
            <h2>Ordini recenti</h2>
        </div>
        <a class="admin-btn secondary admin-btn--icon" href="{{ route('admin.orders.index') }}" aria-label="Vedi ordini" title="Vedi ordini">
            <i class="bi bi-arrow-up-right" aria-hidden="true"></i>
        </a>
    </section>

    <section class="admin-recent-orders" aria-label="Ordini recenti">
        @forelse ($latestOrders as $order)
            <article @class(['admin-recent-order', 'is-aging' => $order->created_at->lte(now()->subMinutes(10))])>
                <header>
                    <div>
                        <span class="badge {{ $order->status }}">{{ $order->statusLabel() }}</span>
                        <h3>{{ $order->customer_name }}</h3>
                        <small>{{ $order->slug }}</small>
                    </div>
                    <strong>€ {{ number_format($order->total_price, 2, ',', '.') }}</strong>
                </header>
                <x-admin.order-items-list :items="$order->items" compact />
                <footer>
                    <span><i class="bi bi-hash" aria-hidden="true"></i> Tavolo {{ $order->table_number }} · riferimento</span>
                    <time datetime="{{ $order->created_at->toIso8601String() }}">{{ $order->created_at->format('H:i') }}</time>
                </footer>
            </article>
        @empty
            <div class="admin-empty-state">
                <i class="bi bi-cup-hot" aria-hidden="true"></i>
                <strong>Banco tranquillo</strong>
                <span>Nessun ordine in attesa.</span>
            </div>
        @endforelse
    </section>
</x-app-layout>
