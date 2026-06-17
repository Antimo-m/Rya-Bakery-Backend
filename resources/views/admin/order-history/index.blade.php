<x-app-layout :title="$title">
    <x-slot name="header">
        <div>
            <span class="admin-kicker">Archivio operativo</span>
            <h1>Storico ordini</h1>
        </div>
    </x-slot>

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
