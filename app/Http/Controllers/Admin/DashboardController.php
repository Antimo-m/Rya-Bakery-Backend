<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminChecklistItem;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $filters = $request->validate([
            'checklist_date' => ['nullable', 'date_format:Y-m-d'],
        ]);
        $checklistDate = isset($filters['checklist_date'])
            ? Carbon::createFromFormat('!Y-m-d', $filters['checklist_date'])
            : now()->startOfDay();

        Order::archiveDeliveredOrders();

        $latestOrders = Order::active()->with('items.product')->latest()->limit(8)->get();

        return view('admin.dashboard', [
            'title' => 'Rya Bakery Admin | Dashboard',
            'ordersTotal' => Order::active()->count(),
            'ordersPending' => Order::active()->awaiting()->count(),
            'ordersAccepted' => Order::active()->where('status', Order::STATUS_PENDING)->count(),
            'ordersCancelled' => Order::where('status', Order::STATUS_CANCELLED)->count(),
            'productsTotal' => Product::count(),
            'productsAvailable' => Product::where('is_active', true)->where('is_available', true)->count(),
            'revenueTotal' => Order::where('status', Order::STATUS_DELIVERED)->sum('total_price'),
            'latestOrders' => $latestOrders,
            'checklistDate' => $checklistDate,
            'dashboardChecklist' => AdminChecklistItem::query()
                ->whereDate('checklist_date', $checklistDate)
                ->orderBy('position')
                ->orderBy('id')
                ->get(),
        ]);
    }
}
