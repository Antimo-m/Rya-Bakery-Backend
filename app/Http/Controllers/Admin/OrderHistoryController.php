<?php

namespace App\Http\Controllers\Admin;

use App\Events\OrderStatusUpdated;
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
            fputcsv($handle, ['Ordine', 'Cliente', 'Tavolo', 'Stato ordine', 'Totale', 'Data ordine', 'Archiviato']);

            $this->historyQuery($filters)
                ->latest('archived_at')
                ->chunk(500, function ($histories) use ($handle): void {
                    foreach ($histories as $history) {
                        fputcsv($handle, [
                            $this->csvCell($history->order->slug),
                            $this->csvCell($history->order->customer_name),
                            $this->csvCell($history->order->table_number),
                            $this->csvCell($history->order->statusLabel()),
                            $this->csvCell($history->order->total_price),
                            $this->csvCell($history->order->created_at?->format('Y-m-d H:i:s')),
                            $this->csvCell($history->archived_at?->format('Y-m-d H:i:s')),
                        ]);
                    }
                });

            fclose($handle);
        }, $fileName, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function report(Request $request): View
    {
        $filters = $this->validatedFilters($request);

        Order::archiveDeliveredOrders();

        return view('admin.order-history.report', [
            'title' => 'Rya Bakery Admin | Report storico ordini',
            'histories' => $this->historyQuery($filters)
                ->latest('archived_at')
                ->limit(250)
                ->get(),
            'filters' => $filters,
            'generatedAt' => now(),
        ]);
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
            'accepted_at' => null,
            'cancelled_at' => null,
            'delivered_at' => null,
        ]);

        $history->update(['restored_at' => now()]);

        try {
            event(new OrderStatusUpdated($order->load('items.product')));
        } catch (\Throwable $exception) {
            report($exception);
        }

        return redirect()->route('admin.orders.index')->with('success', 'Ordine ripristinato negli ordini attivi.');
    }

    private function validatedFilters(Request $request): array
    {
        return $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', 'string', 'in:'.implode(',', [Order::STATUS_CANCELLED, Order::STATUS_DELIVERED])],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);
    }

    private function historyQuery(array $filters)
    {
        return OrderHistory::query()
            ->with('order.items.product')
            ->whereNull('restored_at')
            ->when($filters['status'] ?? null, function ($query, string $status): void {
                $query->whereHas('order', fn ($orderQuery) => $orderQuery->where('status', $status));
            })
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

    private function csvCell(mixed $value): string
    {
        $value = (string) $value;

        return preg_match('/^[=\-+@]/', $value) === 1 ? "'".$value : $value;
    }
}
