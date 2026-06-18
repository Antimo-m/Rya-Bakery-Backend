<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderHistory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OrderHistoryController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $this->validatedFilters($request);

        Order::archiveDeliveredOrders();
        $histories = $this->historyQuery($filters)
            ->latest('archived_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.order-history.index', [
            'title' => 'Rya Bakery Admin | Storico ordini',
            'histories' => $histories,
            'filters' => $filters,
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $filters = $this->validatedFilters($request);

        Order::archiveDeliveredOrders();

        $fileName = 'storico-ordini-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () use ($filters): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Ordine', 'Cliente', 'Tavolo', 'Motivo', 'Totale', 'Archiviato']);

            $this->historyQuery($filters)
                ->latest('archived_at')
                ->chunk(500, function ($histories) use ($handle): void {
                    foreach ($histories as $history) {
                        fputcsv($handle, [
                            $history->order->slug,
                            $history->order->customer_name,
                            $history->order->table_number,
                            $history->reasonLabel(),
                            $history->order->total_price,
                            $history->archived_at?->format('Y-m-d H:i:s'),
                        ]);
                    }
                });

            fclose($handle);
        }, $fileName, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function restore(Order $order): RedirectResponse
    {
        $history = $order->activeHistory;

        if (! $history) {
            return back()->withErrors(['history' => 'Questo ordine non si trova nello storico attivo.']);
        }

        if (! $history->canRestore()) {
            return back()->withErrors(['history' => 'Questo ordine non puo piu essere ripristinato.']);
        }

        $order->update([
            'status' => Order::STATUS_RECEIVED,
            'cancelled_at' => null,
            'delivered_at' => null,
        ]);

        $history->update(['restored_at' => now()]);

        return redirect()->route('admin.orders.index')->with('success', 'Ordine ripristinato negli ordini attivi.');
    }

    private function validatedFilters(Request $request): array
    {
        return $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'reason' => ['nullable', 'string', 'in:'.implode(',', [OrderHistory::REASON_CANCELLED, OrderHistory::REASON_DELIVERED])],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);
    }

    private function historyQuery(array $filters)
    {
        return OrderHistory::query()
            ->with('order.items.product')
            ->whereNull('restored_at')
            ->when($filters['reason'] ?? null, fn ($query, $reason) => $query->where('reason', $reason))
            ->when($filters['from'] ?? null, fn ($query, $from) => $query->whereDate('archived_at', '>=', $from))
            ->when($filters['to'] ?? null, fn ($query, $to) => $query->whereDate('archived_at', '<=', $to))
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $search = trim($search);

                $query->whereHas('order', function ($query) use ($search): void {
                    $query
                        ->where('slug', 'like', "%{$search}%")
                        ->orWhere('customer_name', 'like', "%{$search}%")
                        ->orWhere('table_number', $search);
                });
            });
    }
}
