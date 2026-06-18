<x-app-layout :title="$title">
    <x-slot name="header">
        <div>
            <span class="admin-kicker">Archivio operativo</span>
            <h1>Storico ordini</h1>
        </div>
        <a class="admin-btn secondary" href="{{ route('admin.order-history.export', request()->query()) }}">Esporta CSV</a>
    </x-slot>

    <form class="admin-filters" method="GET" action="{{ route('admin.order-history.index') }}">
        <label>
            Cerca
            <input name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Cliente, tavolo o codice ordine">
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
            <input name="from" type="date" value="{{ $filters['from'] ?? '' }}">
        </label>
        <label>
            A
            <input name="to" type="date" value="{{ $filters['to'] ?? '' }}">
        </label>
        <button class="admin-btn" type="submit">Filtra</button>
        <a class="admin-btn secondary" href="{{ route('admin.order-history.index') }}">Reset</a>
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
                            @foreach ($history->order->items as $item)
                                {{ $item->quantity }}x {{ $item->product->name }}<br>
                            @endforeach
                        </td>
                        <td><span class="badge {{ $history->reason }}">{{ $history->reasonLabel() }}</span></td>
                        <td>
                            @if ($history->restorable_until)
                                {{ $history->canRestore() ? 'Disponibile fino a '.$history->restorable_until->format('H:i') : 'Scaduto' }}
                            @else
                                Non disponibile
                            @endif
                        </td>
                        <td>{{ $history->archived_at->format('d/m/Y H:i') }}</td>
                        <td>
                            @if ($history->canRestore())
                                <form method="POST" action="{{ route('admin.order-history.restore', $history->order) }}" data-confirm="Ripristinare questo ordine negli ordini attivi?">
                                    @csrf
                                    @method('PATCH')
                                    <button class="admin-btn success" type="submit">Ripristina</button>
                                </form>
                            @else
                                <span class="badge off">Non ripristinabile</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6">Nessun ordine nello storico.</td></tr>
                @endforelse
            </tbody>
        </table>
    </section>

    <div style="margin-top: 18px">{{ $histories->links() }}</div>
</x-app-layout>
