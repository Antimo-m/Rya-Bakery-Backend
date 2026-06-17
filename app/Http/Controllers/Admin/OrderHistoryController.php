<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class OrderHistoryController extends Controller
{
    public function index(): View
    {
        Order::archiveDeliveredOrders();

        return view('admin.order-history.index', [
            'title' => 'Rya Bakery Admin | Storico ordini',
            'histories' => \App\Models\OrderHistory::with('order.items.product')
                ->whereNull('restored_at')
                ->latest('archived_at')
                ->paginate(15),
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
            'cancelled_at' => null,
            'delivered_at' => null,
        ]);

        $history->update(['restored_at' => now()]);

        return redirect()->route('admin.orders.index')->with('success', 'Ordine ripristinato negli ordini attivi.');
    }
}
