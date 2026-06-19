<x-app-layout :title="$title">
    <x-slot name="header">
        <div>
            <span class="admin-kicker">Archivio operativo</span>
            <h1>Storico ordini</h1>
        </div>
        <a class="admin-btn secondary admin-export-action" href="{{ route('admin.order-history.export', request()->query()) }}" aria-label="Esporta storico ordini in CSV" title="Esporta storico ordini">
            <span>Esporta storico ordini</span>
            <iconify-icon icon="solar:download-square-bold-duotone"></iconify-icon>
        </a>
    </x-slot>

    <form class="admin-filters" method="GET" action="{{ route('admin.order-history.index') }}">
        <label>
            Cerca
            <input name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Cerca cliente, tavolo o codice ordine">
        </label>
        <label>
            Motivo
            <select name="reason" data-custom-select>
                <option value="">Tutti</option>
                <option value="{{ \App\Models\OrderHistory::REASON_CANCELLED }}" @selected(($filters['reason'] ?? '') === \App\Models\OrderHistory::REASON_CANCELLED)>Annullati</option>
                <option value="{{ \App\Models\OrderHistory::REASON_DELIVERED }}" @selected(($filters['reason'] ?? '') === \App\Models\OrderHistory::REASON_DELIVERED)>Completati</option>
            </select>
        </label>
        <label>
            Da
            <span class="custom-date-field">
                <iconify-icon icon="solar:calendar-date-bold-duotone"></iconify-icon>
                <input name="from" type="text" inputmode="numeric" pattern="\d{4}-\d{2}-\d{2}" placeholder="YYYY-MM-DD" value="{{ $filters['from'] ?? '' }}">
            </span>
        </label>
        <label>
            A
            <span class="custom-date-field">
                <iconify-icon icon="solar:calendar-date-bold-duotone"></iconify-icon>
                <input name="to" type="text" inputmode="numeric" pattern="\d{4}-\d{2}-\d{2}" placeholder="YYYY-MM-DD" value="{{ $filters['to'] ?? '' }}">
            </span>
        </label>
        <button class="admin-btn" type="submit">Applica filtri</button>
        <a class="admin-btn secondary" href="{{ route('admin.order-history.index') }}">Pulisci</a>
    </form>

    <section class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Ordine</th>
                    <th>Prodotti</th>
                    <th>Motivo</th>
                    <th>Ripristino</th>
                    <th>Archiviato</th>
                    <th>Azioni</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($histories as $history)
                    <tr>
                        <td>
                            <strong>{{ $history->order->customer_name }}</strong><br>
                            <small>Tavolo {{ $history->order->table_number }} · {{ $history->order->slug }}</small>
                        </td>
                        <td>
                            <x-admin.order-products :items="$history->order->items" />
                        </td>
                        <td><span class="badge {{ $history->reason }}">{{ $history->reasonLabel() }}</span></td>
                        <td>
                            @if ($history->restorable_until)
                                {{ $history->canRestore() ? 'Recuperabile fino alle '.$history->restorable_until->format('H:i') : 'Tempo scaduto' }}
                            @else
                                Archiviato definitivamente
                            @endif
                        </td>
                        <td>{{ $history->archived_at->format('d/m/Y H:i') }}</td>
                        <td>
                            @if ($history->canRestore())
                                <form method="POST" action="{{ route('admin.order-history.restore', $history->order) }}" data-confirm="Ripristinare questo ordine negli ordini attivi?">
                                    @csrf
                                    @method('PATCH')
                                    <button class="admin-btn success admin-btn--icon" type="submit" aria-label="Ripristina ordine" title="Ripristina">
                                        <iconify-icon icon="solar:restart-circle-bold-duotone"></iconify-icon>
                                    </button>
                                </form>
                            @else
                                <span class="badge off">Non ripristinabile</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6">Lo storico e ancora vuoto: qui troverai ordini completati e annullati.</td></tr>
                @endforelse
            </tbody>
        </table>
    </section>

    @if ($histories->hasPages())
        <footer class="admin-products-footer">
            <nav class="admin-pagination" aria-label="Paginazione storico ordini">
                @if ($histories->onFirstPage())
                    <span class="admin-pagination__button is-disabled" aria-disabled="true">
                        <iconify-icon icon="solar:alt-arrow-left-linear"></iconify-icon>
                    </span>
                @else
                    <a class="admin-pagination__button" href="{{ $histories->previousPageUrl() }}" rel="prev" aria-label="Pagina precedente">
                        <iconify-icon icon="solar:alt-arrow-left-linear"></iconify-icon>
                    </a>
                @endif

                <span class="admin-pagination__summary" aria-current="page">Pagina {{ $histories->currentPage() }} di {{ $histories->lastPage() }}</span>

                @if ($histories->hasMorePages())
                    <a class="admin-pagination__button" href="{{ $histories->nextPageUrl() }}" rel="next" aria-label="Pagina successiva">
                        <iconify-icon icon="solar:alt-arrow-right-linear"></iconify-icon>
                    </a>
                @else
                    <span class="admin-pagination__button is-disabled" aria-disabled="true">
                        <iconify-icon icon="solar:alt-arrow-right-linear"></iconify-icon>
                    </span>
                @endif
            </nav>
        </footer>
    @endif
</x-app-layout>
