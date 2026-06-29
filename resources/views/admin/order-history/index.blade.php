<x-app-layout :title="$title">
    <x-slot name="header">
        <div>
            <span class="admin-kicker">Archivio operativo</span>
            <h1>Storico ordini</h1>
        </div>
        <div class="admin-header-actions">
            <a class="admin-btn secondary admin-export-action" href="{{ route('admin.order-history.report', request()->query()) }}" aria-label="Apri report storico ordini per PDF" title="Report PDF">
                <span>Report PDF</span>
                <i class="bi bi-printer" aria-hidden="true"></i>
            </a>
            <a class="admin-btn secondary admin-export-action" href="{{ route('admin.order-history.export', request()->query()) }}" aria-label="Esporta storico ordini in CSV" title="Esporta storico ordini">
                <span>CSV storico</span>
                <i class="bi bi-download" aria-hidden="true"></i>
            </a>
        </div>
    </x-slot>

    <form class="admin-filters" method="GET" action="{{ route('admin.order-history.index') }}">
        <label>
            Cerca
            <input name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Cerca cliente, tavolo o codice ordine">
        </label>
        <label>
            Stato ordine
            <select name="status" data-custom-select>
                <option value="">Tutti gli stati</option>
                <option value="{{ \App\Models\Order::STATUS_CANCELLED }}" @selected(($filters['status'] ?? '') === \App\Models\Order::STATUS_CANCELLED)>Annullato</option>
                <option value="{{ \App\Models\Order::STATUS_DELIVERED }}" @selected(($filters['status'] ?? '') === \App\Models\Order::STATUS_DELIVERED)>Pronto per il ritiro</option>
            </select>
        </label>
        <label>
            Da
            <span class="custom-date-field">
                <i class="bi bi-calendar3" aria-hidden="true"></i>
                <input name="from" type="text" inputmode="none" pattern="\d{4}-\d{2}-\d{2}" placeholder="YYYY-MM-DD" value="{{ $filters['from'] ?? '' }}" readonly>
            </span>
        </label>
        <label>
            A
            <span class="custom-date-field">
                <i class="bi bi-calendar3" aria-hidden="true"></i>
                <input name="to" type="text" inputmode="none" pattern="\d{4}-\d{2}-\d{2}" placeholder="YYYY-MM-DD" value="{{ $filters['to'] ?? '' }}" readonly>
            </span>
        </label>
        <button class="admin-btn" type="submit">Applica filtri</button>
        <a class="admin-btn secondary" href="{{ route('admin.order-history.index') }}">Pulisci</a>
    </form>

    <section class="history-card-list" aria-label="Storico ordini">
        @forelse ($histories as $history)
            <article @class(['history-order-card', 'is-restorable' => $history->canRestore()])>
                <header class="history-order-card__header">
                    <div>
                        <span class="badge {{ $history->order->status }}">{{ $history->order->statusLabel() }}</span>
                        <h2>{{ $history->order->customer_name }}</h2>
                        <small>{{ $history->order->slug }}</small>
                    </div>
                    <strong>€ {{ number_format($history->order->total_price, 2, ',', '.') }}</strong>
                </header>
                <div class="history-order-card__meta">
                    <span><i class="bi bi-hash" aria-hidden="true"></i> Tavolo {{ $history->order->table_number }} · solo riferimento</span>
                    <span><i class="bi bi-calendar3" aria-hidden="true"></i> Ordinato {{ $history->order->created_at?->format('d/m/Y H:i') }}</span>
                    <span><i class="bi bi-archive" aria-hidden="true"></i> Archiviato {{ $history->archived_at->format('d/m/Y H:i') }}</span>
                </div>
                <x-admin.order-items-list :items="$history->order->items" />
                <footer class="history-order-card__footer">
                    <div>
                        @if ($history->restorable_until)
                            <strong>{{ $history->canRestore() ? 'Ripristino disponibile' : 'Tempo di ripristino scaduto' }}</strong>
                            <small>{{ $history->canRestore() ? 'Fino alle '.$history->restorable_until->format('H:i') : 'Ordine archiviato definitivamente' }}</small>
                        @else
                            <strong>Ordine pronto per il ritiro</strong>
                            <small>Archiviazione definitiva</small>
                        @endif
                    </div>
                    @if ($history->canRestore())
                        <form method="POST" action="{{ route('admin.order-history.restore', $history->order) }}" data-confirm="Ripristinare questo ordine negli ordini attivi?">
                            @csrf
                            @method('PATCH')
                            <button class="admin-btn success" type="submit">
                                <i class="bi bi-arrow-counterclockwise" aria-hidden="true"></i>
                                <span>Ripristina</span>
                            </button>
                        </form>
                    @else
                        <span class="badge off">Non ripristinabile</span>
                    @endif
                </footer>
            </article>
        @empty
            <div class="admin-empty-state">
                <i class="bi bi-clock-history" aria-hidden="true"></i>
                <strong>Storico ancora vuoto</strong>
                <span>Qui troverai gli ordini pronti e annullati.</span>
            </div>
        @endforelse
    </section>

    <x-admin.pagination :paginator="$histories" label="Paginazione storico ordini" />
</x-app-layout>
