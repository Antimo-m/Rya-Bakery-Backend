<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnalysisController extends Controller
{
    public function __invoke(Request $request): View
    {
        $filters = $request->validate([
            'day' => ['nullable', 'date'],
            'month' => ['nullable', 'date_format:Y-m'],
        ]);

        $day = isset($filters['day'])
            ? Carbon::parse($filters['day'])->startOfDay()
            : now()->startOfDay();
        $month = isset($filters['month'])
            ? Carbon::parse($filters['month'].'-01')->startOfMonth()
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
        ]);
    }
}
