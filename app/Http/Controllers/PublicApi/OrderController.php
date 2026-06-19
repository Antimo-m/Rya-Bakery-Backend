<?php

namespace App\Http\Controllers\PublicApi;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:80'],
            'table_number' => ['required', 'integer', 'min:1', 'max:999'],
            'notes' => ['nullable', 'string', 'max:500'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_slug' => ['required', 'string', 'exists:products,slug'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:'.Order::MAX_PRODUCT_QUANTITY],
        ], [
            'customer_name.required' => 'Inserisci il nome cliente per inviare l ordine.',
            'table_number.required' => 'Inserisci il numero del tavolo.',
            'items.required' => 'Aggiungi almeno un prodotto al carrello.',
        ]);

        $slugs = collect($validated['items'])->pluck('product_slug')->all();
        $products = Product::query()
            ->whereIn('slug', $slugs)
            ->where('is_active', true)
            ->where('is_available', true)
            ->get()
            ->keyBy('slug');

        if ($products->count() !== count(array_unique($slugs))) {
            throw ValidationException::withMessages([
                'items' => 'Uno o piu prodotti non sono disponibili al momento.',
            ]);
        }

        $order = DB::transaction(function () use ($validated, $products): Order {
            $order = Order::create([
                'slug' => $this->makeOrderSlug(),
                'customer_name' => $validated['customer_name'],
                'table_number' => $validated['table_number'],
                'notes' => $validated['notes'] ?? null,
                'status' => Order::STATUS_RECEIVED,
            ]);

            foreach ($validated['items'] as $item) {
                $product = $products[$item['product_slug']];
                $quantity = (int) $item['quantity'];
                $unitPrice = (float) $product->price;

                $order->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'line_total' => $unitPrice * $quantity,
                ]);
            }

            $order->recalculateTotal();

            return $order->load('items.product');
        });

        return response()->json([
            'message' => 'Ordine inviato correttamente. Lo staff lo vedra tra gli ordini in attesa.',
            'order' => $this->payload($order),
        ], 201);
    }

    public function show(Order $order): JsonResponse
    {
        return response()->json([
            'order' => $this->payload($order->load('items.product')),
        ]);
    }

    private function makeOrderSlug(): string
    {
        do {
            $slug = 'ordine-'.now()->format('Ymd-His').'-'.Str::lower(Str::random(5));
        } while (Order::where('slug', $slug)->exists());

        return $slug;
    }

    private function payload(Order $order): array
    {
        return [
            'slug' => $order->slug,
            'customer_name' => $order->customer_name,
            'table_number' => $order->table_number,
            'status' => $order->status,
            'status_label' => $order->statusLabel(),
            'total_price' => (float) $order->total_price,
            'created_at' => $order->created_at?->toIso8601String(),
            'items' => $order->items->map(fn ($item): array => [
                'product_slug' => $item->product->slug,
                'product_name' => $item->product->name,
                'product_image_url' => $item->product->image_url,
                'quantity' => $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'line_total' => (float) $item->line_total,
            ]),
        ];
    }
}
