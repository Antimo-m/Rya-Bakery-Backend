<?php

namespace App\Http\Controllers\PublicApi;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
                'name' => $product->name,
                'slug' => $product->slug,
                'category' => $product->category,
                'description' => $product->description,
                'price' => (float) $product->price,
                'image_url' => $product->image_url,
                'is_available' => $product->is_available,
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
                'name' => $product->name,
                'slug' => $product->slug,
                'category' => $product->category,
                'description' => $product->description,
                'price' => (float) $product->price,
                'image_url' => $product->image_url,
                'is_available' => $product->is_available,
            ],
        ]);
    }
}
