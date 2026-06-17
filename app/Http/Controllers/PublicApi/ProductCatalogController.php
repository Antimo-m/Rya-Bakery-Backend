<?php

namespace App\Http\Controllers\PublicApi;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;

class ProductCatalogController extends Controller
{
    public function index(): JsonResponse
    {
        $products = Product::query()
            ->where('is_active', true)
            ->orderBy('category')
            ->orderBy('name')
            ->get()
            ->map(fn (Product $product): array => [
                'name' => $product->name,
                'slug' => $product->slug,
                'category' => $product->category,
                'description' => $product->description,
                'price' => (float) $product->price,
                'image_url' => $product->image_url,
                'is_available' => $product->is_available,
            ]);

        return response()->json([
            'products' => $products,
            'categories' => $products->pluck('category')->filter()->unique()->values(),
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
