<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(): View
    {
        return view('admin.products.index', [
            'title' => 'Rya Bakery Admin | Prodotti',
            'products' => Product::query()->latest()->paginate(12),
        ]);
    }

    public function create(): View
    {
        return view('admin.products.form', [
            'title' => 'Rya Bakery Admin | Nuovo prodotto',
            'product' => new Product(['is_active' => true, 'is_available' => true]),
            'categories' => $this->categories(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['slug'] = $this->slug($data['name']);
        $data['is_available'] = $request->boolean('is_available');
        $data['is_active'] = $request->boolean('is_active');
        $data['is_best_seller'] = $request->boolean('is_best_seller');
        $data['is_new'] = $request->boolean('is_new');
        $data['is_freshly_baked'] = $request->boolean('is_freshly_baked');
        $data['image_path'] = $request->file('image')?->store('products', 'public');

        Product::create($data);

        return redirect()->route('admin.products.index')->with('success', 'Prodotto aggiunto al banco digitale.');
    }

    public function edit(Product $product): View
    {
        return view('admin.products.form', [
            'title' => 'Rya Bakery Admin | Modifica prodotto',
            'product' => $product,
            'categories' => $this->categories(),
        ]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $data = $this->validated($request, $product);
        $data['slug'] = $this->slug($data['name'], $product);
        $data['is_available'] = $request->boolean('is_available');
        $data['is_active'] = $request->boolean('is_active');
        $data['is_best_seller'] = $request->boolean('is_best_seller');
        $data['is_new'] = $request->boolean('is_new');
        $data['is_freshly_baked'] = $request->boolean('is_freshly_baked');

        if ($request->hasFile('image')) {
            if ($product->image_path) {
                Storage::disk('public')->delete($product->image_path);
            }

            $data['image_path'] = $request->file('image')->store('products', 'public');
        }

        $product->update($data);

        return redirect()->route('admin.products.index')->with('success', 'Prodotto aggiornato nel catalogo.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        if ($product->image_path) {
            Storage::disk('public')->delete($product->image_path);
        }

        $product->delete();

        return redirect()->route('admin.products.index')->with('success', 'Prodotto rimosso dal catalogo.');
    }

    private function validated(Request $request, ?Product $product = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'category' => ['nullable', 'string', 'max:80'],
            'description' => ['nullable', 'string', 'max:1000'],
            'price' => ['required', 'numeric', 'min:0.1', 'max:9999'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'is_available' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'is_best_seller' => ['nullable', 'boolean'],
            'is_new' => ['nullable', 'boolean'],
            'is_freshly_baked' => ['nullable', 'boolean'],
        ], [
            'name.required' => 'Inserisci il nome del prodotto.',
            'price.required' => 'Inserisci il prezzo del prodotto.',
            'image.image' => 'Carica un file immagine valido.',
        ]);
    }

    private function slug(string $name, ?Product $product = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $counter = 2;

        while (Product::where('slug', $slug)
            ->when($product, fn ($query) => $query->whereKeyNot($product->id))
            ->exists()) {
            $slug = $base.'-'.$counter++;
        }

        return $slug;
    }

    private function categories(): array
    {
        return Product::query()->whereNotNull('category')->distinct()->orderBy('category')->pluck('category')->all();
    }
}
