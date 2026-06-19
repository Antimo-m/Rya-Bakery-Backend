<?php

namespace App\Http\Controllers\Admin;

use App\Events\OrderStatusUpdated;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderHistory;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(): View
    {
        Order::archiveDeliveredOrders();

        return view('admin.orders.index', [
            'title' => 'Rya Bakery Admin | Ordini',
            'orders' => Order::active()->with('items.product')->latest()->paginate(15),
        ]);
    }

    public function liveIndex(): JsonResponse
    {
        Order::archiveDeliveredOrders();

        return response()->json([
            'orders' => Order::active()
                ->with('items.product')
                ->latest()
                ->limit(20)
                ->get()
                ->map(fn (Order $order): array => $this->livePayload($order))
                ->all(),
        ]);
    }

    public function edit(Order $order): View
    {
        return view('admin.orders.form', [
            'title' => 'Rya Bakery Admin | Modifica ordine',
            'order' => $order->load('items.product'),
            'products' => Product::where('is_active', true)->orderBy('name')->get(),
            'statuses' => Order::STATUSES,
        ]);
    }

    public function update(Request $request, Order $order): RedirectResponse
    {
        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:80'],
            'table_number' => ['required', 'integer', 'min:1', 'max:999'],
            'status' => ['required', Rule::in(array_keys(Order::STATUSES))],
            'notes' => ['nullable', 'string', 'max:500'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_slug' => ['nullable', 'string', 'exists:products,slug'],
            'items.*.quantity' => ['nullable', 'integer', 'min:0', 'max:'.Order::MAX_PRODUCT_QUANTITY],
        ]);

        $data['items'] = collect($data['items'])
            ->filter(fn (array $item): bool => filled($item['product_slug'] ?? null) && (int) ($item['quantity'] ?? 0) > 0)
            ->values()
            ->all();

        if ($data['items'] === []) {
            return back()
                ->withErrors(['items' => 'Un ordine deve contenere almeno un prodotto con quantita maggiore di zero.'])
                ->withInput();
        }

        $previousStatus = $order->status;

        DB::transaction(function () use ($order, $data): void {
            $order->update([
                'customer_name' => $data['customer_name'],
                'table_number' => $data['table_number'],
                'status' => $data['status'],
                'notes' => $data['notes'] ?? null,
            ]);

            $order->items()->delete();
            $products = Product::whereIn('slug', collect($data['items'])->pluck('product_slug'))->get()->keyBy('slug');

            foreach ($data['items'] as $item) {
                $product = $products[$item['product_slug']];
                $unitPrice = (float) $product->price;

                $order->items()->create([
                    'product_id' => $product->id,
                    'quantity' => (int) $item['quantity'],
                    'unit_price' => $unitPrice,
                    'line_total' => $unitPrice * (int) $item['quantity'],
                ]);
            }

            $order->recalculateTotal();
        });

        if ($order->status !== $previousStatus) {
            $this->broadcastStatus($order->load('items.product'));
        }

        return redirect()->route('admin.orders.index')->with('success', 'Ordine aggiornato al banco.');
    }

    public function accept(Order $order): RedirectResponse
    {
        if ($order->status !== Order::STATUS_RECEIVED) {
            return back()->withErrors(['order' => 'Solo gli ordini ricevuti possono essere accettati.']);
        }

        $order->update([
            'status' => Order::STATUS_PENDING,
            'accepted_at' => now(),
        ]);

        $this->broadcastStatus($order->load('items.product'));

        return back()->with('success', 'Ordine preso in preparazione.');
    }

    public function cancel(Order $order): RedirectResponse
    {
        if (! in_array($order->status, [Order::STATUS_RECEIVED, Order::STATUS_PENDING], true)) {
            return back()->withErrors(['order' => 'Questo ordine non puo essere annullato.']);
        }

        DB::transaction(function () use ($order): void {
            $order->update([
                'status' => Order::STATUS_CANCELLED,
                'cancelled_at' => now(),
            ]);

            $order->histories()->create([
                'reason' => OrderHistory::REASON_CANCELLED,
                'archived_at' => now(),
                'restorable_until' => now()->addMinutes(30),
            ]);
        });

        $this->broadcastStatus($order->load('items.product'));

        return redirect()->route('admin.order-history.index')->with('success', 'Ordine annullato e spostato nello storico.');
    }

    public function complete(Order $order): RedirectResponse
    {
        if ($order->status !== Order::STATUS_PENDING) {
            return back()->withErrors(['order' => 'Accetta l ordine prima di completarlo.']);
        }

        $order->update([
            'status' => Order::STATUS_DELIVERED,
            'delivered_at' => now(),
        ]);

        $this->broadcastStatus($order->load('items.product'));

        return back()->with('success', 'Ordine pronto e completato. Restera negli ordini attivi per 10 minuti.');
    }

    public function destroy(Order $order): RedirectResponse
    {
        $order->delete();

        return redirect()->route('admin.orders.index')->with('success', 'Ordine rimosso dal banco operativo.');
    }

    private function livePayload(Order $order): array
    {
        return [
            'id' => $order->id,
            'slug' => $order->slug,
            'customer_name' => $order->customer_name,
            'table_number' => $order->table_number,
            'status' => $order->status,
            'status_label' => $order->statusLabel(),
            'total_price' => (float) $order->total_price,
            'created_at' => $order->created_at?->toIso8601String(),
            'items' => $order->items->map(fn ($item): array => [
                'id' => $item->id,
                'quantity' => $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'line_total' => (float) $item->line_total,
                'product' => [
                    'id' => $item->product?->id,
                    'slug' => $item->product?->slug,
                    'name' => $item->product?->name,
                    'image_url' => $item->product?->image_url,
                ],
            ])->all(),
        ];
    }

    private function broadcastStatus(Order $order): void
    {
        try {
            event(new OrderStatusUpdated($order));
        } catch (\Throwable $exception) {
            report($exception);
        }
    }
}
