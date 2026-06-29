<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class AnalysisController extends Controller
{
    public function __invoke(Request $request): View
    {
        $filters = $request->validate([
            'day' => ['nullable', 'date_format:Y-m-d'],
            'month' => ['nullable', 'date_format:Y-m'],
        ]);

        $day = isset($filters['day'])
            ? Carbon::createFromFormat('!Y-m-d', $filters['day'])
            : now()->startOfDay();
        $month = isset($filters['month'])
            ? Carbon::createFromFormat('!Y-m-d', $filters['month'].'-01')
            : now()->startOfMonth();

        $completedOrders = Order::query()
            ->where('status', Order::STATUS_DELIVERED)
            ->whereNotNull('delivered_at');

        $dayOrders = (clone $completedOrders)
            ->whereBetween('delivered_at', [$day->copy()->startOfDay(), $day->copy()->endOfDay()])
            ->with('items.product')
            ->latest('delivered_at')
            ->get();

        $monthOrders = (clone $completedOrders)
            ->whereBetween('delivered_at', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
            ->with('items.product')
            ->latest('delivered_at')
            ->get();

        $todayTotal = (clone $completedOrders)
            ->whereBetween('delivered_at', [now()->startOfDay(), now()->endOfDay()])
            ->sum('total_price');
        $todayDeliveredOrders = (clone $completedOrders)
            ->whereBetween('delivered_at', [now()->startOfDay(), now()->endOfDay()])
            ->get();

        return view('admin.analysis.index', [
            'title' => 'Rya Bakery Admin | Analisi',
            'filters' => [
                'day' => $day->format('Y-m-d'),
                'month' => $month->format('Y-m'),
            ],
            'todayTotal' => $todayTotal,
            'dayTotal' => $dayOrders->sum('total_price'),
            'monthTotal' => $monthOrders->sum('total_price'),
            'dayOrders' => $dayOrders,
            'monthOrdersCount' => $monthOrders->count(),
            'hourlyRevenue' => $this->hourlyRevenue($todayDeliveredOrders),
        ]);
    }

    private function hourlyRevenue(Collection $orders): Collection
    {
        return collect(range(8, 22))->map(function (int $hour) use ($orders): array {
            $total = $orders
                ->filter(fn (Order $order): bool => (int) $order->delivered_at?->format('G') === $hour)
                ->sum('total_price');

            return [
                'label' => sprintf('%02d:00', $hour),
                'total' => (float) $total,
            ];
        });
    }
}
