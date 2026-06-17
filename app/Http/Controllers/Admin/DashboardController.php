<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        Order::archiveDeliveredOrders();

        return view('admin.dashboard', [
            'title' => 'Rya Bakery Admin | Dashboard',
            'ordersTotal' => Order::active()->count(),
            'ordersPending' => Order::active()->awaiting()->count(),
            'ordersAccepted' => Order::active()->where('status', Order::STATUS_PENDING)->count(),
            'ordersCancelled' => Order::where('status', Order::STATUS_CANCELLED)->count(),
            'productsTotal' => Product::count(),
            'productsAvailable' => Product::where('is_active', true)->where('is_available', true)->count(),
            'revenueTotal' => Order::where('status', Order::STATUS_DELIVERED)->sum('total_price'),
            'latestOrders' => Order::active()->with('items.product')->latest()->limit(8)->get(),
        ]);
    }
}
