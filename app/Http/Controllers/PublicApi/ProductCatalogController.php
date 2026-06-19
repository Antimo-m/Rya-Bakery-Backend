<?php

namespace App\Http\Controllers\PublicApi;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductCatalogController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = max(1, min((int) $request->integer('per_page', 12), 30));

        $query = Product::query()
            ->where('is_active', true)
            ->when($request->filled('category'), fn ($query) => $query->where('category', (string) $request->string('category')))
            ->orderBy('category')
            ->orderBy('name');

        $products = $query->paginate($perPage)->withQueryString();

        return response()->json([
            'products' => $products->getCollection()->map(fn (Product $product): array => [
                ...$this->productPayload($product),
            ]),
            'categories' => Product::query()
                ->where('is_active', true)
                ->whereNotNull('category')
                ->distinct()
                ->orderBy('category')
                ->pluck('category')
                ->values(),
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ],
        ]);
    }

    public function show(Product $product): JsonResponse
    {
        abort_unless($product->is_active, 404);

        return response()->json([
            'product' => [
                ...$this->productPayload($product),
            ],
        ]);
    }

    public function mostOrdered(Request $request): JsonResponse
    {
        $limit = max(1, min((int) $request->integer('limit', 8), 12));
        $orderStats = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.status', '!=', Order::STATUS_CANCELLED)
            ->select('order_items.product_id')
            ->selectRaw('COUNT(DISTINCT order_items.order_id) as orders_count')
            ->selectRaw('COALESCE(SUM(order_items.quantity), 0) as ordered_quantity')
            ->groupBy('order_items.product_id');

        $products = Product::query()
            ->select('products.*')
            ->addSelect([
                'order_stats.orders_count',
                'order_stats.ordered_quantity',
            ])
            ->joinSub($orderStats, 'order_stats', 'order_stats.product_id', '=', 'products.id')
            ->where('products.is_active', true)
            ->orderByDesc('orders_count')
            ->orderByDesc('ordered_quantity')
            ->orderBy('products.name')
            ->limit($limit)
            ->get();

        return response()->json([
            'products' => $products->map(fn (Product $product): array => [
                ...$this->productPayload($product),
                'orders_count' => (int) $product->orders_count,
                'ordered_quantity' => (int) $product->ordered_quantity,
            ]),
        ]);
    }

    private function productPayload(Product $product): array
    {
        return [
            'name' => $product->name,
            'slug' => $product->slug,
            'category' => $product->category,
            'description' => $product->description,
            'price' => (float) $product->price,
            'image_url' => $product->image_url,
            'is_available' => $product->is_available,
            'badges' => $product->specialBadges(),
        ];
    }
}
