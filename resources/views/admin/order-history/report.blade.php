<!DOCTYPE html>
<html lang="it">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $title }}</title>
        <style>
            body { margin: 0; padding: 32px; color: #2f211d; font-family: Arial, sans-serif; background: #fffaf5; }
            header { display: flex; justify-content: space-between; gap: 24px; align-items: flex-start; margin-bottom: 24px; }
            h1 { margin: 0 0 6px; font-size: 28px; }
            p { margin: 0; color: #6f5c51; }
            button { padding: 10px 14px; border: 1px solid #e6d6c8; border-radius: 10px; background: #fff; font-weight: 700; cursor: pointer; }
            table { width: 100%; border-collapse: collapse; background: #fff; }
            th, td { padding: 10px 12px; border-bottom: 1px solid #eadccf; text-align: left; vertical-align: top; font-size: 12px; }
            th { color: #7c4a2f; text-transform: uppercase; }
            .products { max-width: 320px; line-height: 1.45; }
            @media print {
                body { padding: 0; background: #fff; }
                button { display: none; }
            }
        </style>
    </head>
    <body>
        <header>
            <div>
                <h1>Report storico ordini</h1>
                <p>Generato il {{ $generatedAt->format('d/m/Y H:i') }}</p>
            </div>
            <button type="button" onclick="window.print()">Stampa / salva PDF</button>
        </header>

        <table>
            <thead>
                <tr>
                    <th>Ordine</th>
                    <th>Cliente</th>
                    <th>Tavolo</th>
                    <th>Prodotti</th>
                    <th>Stato ordine</th>
                    <th>Totale</th>
                    <th>Data ordine</th>
                    <th>Archiviato</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($histories as $history)
                    <tr>
                        <td>{{ $history->order->slug }}</td>
                        <td>{{ $history->order->customer_name }}</td>
                        <td>{{ $history->order->table_number }}</td>
                        <td class="products">
                            {{ $history->order->items->map(fn ($item) => $item->quantity.'x '.($item->product?->name ?? 'Prodotto'))->join(', ') }}
                        </td>
                        <td>{{ $history->order->statusLabel() }}</td>
                        <td>€ {{ number_format($history->order->total_price, 2, ',', '.') }}</td>
                        <td>{{ $history->order->created_at?->format('d/m/Y H:i') }}</td>
                        <td>{{ $history->archived_at?->format('d/m/Y H:i') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8">Nessun ordine nello storico.</td></tr>
                @endforelse
            </tbody>
        </table>
    </body>
</html>
