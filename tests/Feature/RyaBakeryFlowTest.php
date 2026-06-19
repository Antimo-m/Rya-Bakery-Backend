<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderHistory;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RyaBakeryFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_catalog_returns_products_with_slugs(): void
    {
        $product = Product::factory()->create([
            'name' => 'Focaccia genovese',
            'slug' => 'focaccia-genovese',
            'is_active' => true,
            'is_available' => true,
        ]);

        $this->getJson('/api/products')
            ->assertOk()
            ->assertJsonPath('products.0.slug', $product->slug)
            ->assertJsonMissing(['id' => $product->id]);
    }

    public function test_public_catalog_is_paginated_and_filterable(): void
    {
        Product::factory()->count(3)->create([
            'category' => 'Dolci',
            'is_active' => true,
        ]);
        Product::factory()->count(2)->create([
            'category' => 'Salato',
            'is_active' => true,
        ]);

        $this->getJson('/api/products?category=Dolci&per_page=2')
            ->assertOk()
            ->assertJsonCount(2, 'products')
            ->assertJsonPath('meta.per_page', 2)
            ->assertJsonPath('meta.total', 3)
            ->assertJsonPath('categories.0', 'Dolci')
            ->assertJsonPath('categories.1', 'Salato');
    }

    public function test_public_catalog_returns_most_ordered_products(): void
    {
        $popular = Product::factory()->create([
            'name' => 'Coca Cola',
            'slug' => 'coca-cola',
            'is_active' => true,
        ]);
        $lessPopular = Product::factory()->create([
            'name' => 'Cornetto',
            'slug' => 'cornetto',
            'is_active' => true,
        ]);

        foreach (range(1, 3) as $index) {
            $order = Order::create([
                'slug' => 'ordine-popular-'.$index,
                'customer_name' => 'Cliente '.$index,
                'table_number' => $index,
                'status' => Order::STATUS_DELIVERED,
                'total_price' => 3.00,
                'delivered_at' => now(),
            ]);

            $order->items()->create([
                'product_id' => $popular->id,
                'quantity' => 1,
                'unit_price' => 1.00,
                'line_total' => 1.00,
            ]);
        }

        $order = Order::create([
            'slug' => 'ordine-less-popular',
            'customer_name' => 'Cliente Cornetto',
            'table_number' => 8,
            'status' => Order::STATUS_DELIVERED,
            'total_price' => 2.00,
            'delivered_at' => now(),
        ]);

        $order->items()->create([
            'product_id' => $lessPopular->id,
            'quantity' => 5,
            'unit_price' => 1.00,
            'line_total' => 5.00,
        ]);

        $this->getJson('/api/products/most-ordered?limit=2')
            ->assertOk()
            ->assertJsonPath('products.0.slug', 'coca-cola')
            ->assertJsonPath('products.0.orders_count', 3)
            ->assertJsonPath('products.1.slug', 'cornetto');
    }

    public function test_frontend_can_create_order_using_product_slug(): void
    {
        $product = Product::factory()->create([
            'slug' => 'cappuccino-rya',
            'price' => 2.20,
            'is_active' => true,
            'is_available' => true,
        ]);

        $this->postJson('/api/orders', [
            'customer_name' => 'Naomi',
            'table_number' => 4,
            'items' => [
                ['product_slug' => $product->slug, 'quantity' => 2],
            ],
        ])
            ->assertCreated()
            ->assertJsonPath('order.customer_name', 'Naomi')
            ->assertJsonPath('order.table_number', 4)
            ->assertJsonPath('order.items.0.product_slug', 'cappuccino-rya')
            ->assertJsonPath('order.items.0.product_image_url', $product->image_url)
            ->assertJsonPath('order.total_price', 4.4);

        $this->assertDatabaseHas('orders', [
            'customer_name' => 'Naomi',
            'table_number' => 4,
            'status' => Order::STATUS_RECEIVED,
        ]);
    }

    public function test_public_order_rejects_more_than_20_units_per_product(): void
    {
        $product = Product::factory()->create([
            'slug' => 'box-pasticceria',
            'is_active' => true,
            'is_available' => true,
        ]);

        $this->postJson('/api/orders', [
            'customer_name' => 'Naomi',
            'table_number' => 4,
            'items' => [
                ['product_slug' => $product->slug, 'quantity' => 21],
            ],
        ])->assertUnprocessable();
    }

    public function test_admin_can_view_received_orders(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create([
            'name' => 'Cornetto crema',
            'slug' => 'cornetto-crema',
        ]);
        $order = Order::create([
            'slug' => 'ordine-test',
            'customer_name' => 'Cliente Test',
            'table_number' => 7,
            'status' => Order::STATUS_RECEIVED,
            'total_price' => 1.80,
        ]);

        $order->items()->create([
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => 1.80,
            'line_total' => 1.80,
        ]);

        $editUrl = route('admin.orders.edit', $order);

        $this->assertStringNotContainsString('/edit', $editUrl);
        $this->assertStringContainsString('/scheda', $editUrl);

        $this->actingAs($user)
            ->get(route('admin.orders.index'))
            ->assertOk()
            ->assertSee('Cliente Test')
            ->assertSee('Tavolo')
            ->assertSee('ordine-test')
            ->assertSee('>7<', false)
            ->assertSee('Cornetto');
    }

    public function test_admin_routes_do_not_expose_backend_admin_prefix(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk();

        $this->actingAs($user)
            ->get('/backend/admin')
            ->assertRedirect('/dashboard');
    }

    public function test_admin_analysis_shows_completed_revenue_for_selected_day(): void
    {
        $user = User::factory()->create();
        $deliveredAt = now()->setDate(2026, 6, 10)->setTime(12, 30);

        Order::create([
            'slug' => 'ordine-analisi',
            'customer_name' => 'Cliente Analisi',
            'table_number' => 2,
            'status' => Order::STATUS_DELIVERED,
            'total_price' => 12.50,
            'delivered_at' => $deliveredAt,
        ]);

        Order::create([
            'slug' => 'ordine-non-contato',
            'customer_name' => 'Cliente Attesa',
            'table_number' => 3,
            'status' => Order::STATUS_RECEIVED,
            'total_price' => 99.00,
        ]);

        $this->actingAs($user)
            ->get(route('admin.analysis.index', ['day' => '2026-06-10', 'month' => '2026-06']))
            ->assertOk()
            ->assertSee('€ 12,50')
            ->assertSee('Cliente Analisi')
            ->assertDontSee('€ 99,00');
    }

    public function test_application_uses_italy_timezone(): void
    {
        $this->assertSame('Europe/Rome', config('app.timezone'));
    }

    public function test_admin_can_manage_products_with_slugs(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('admin.products.store'), [
                'name' => 'Maritozzo crema',
                'category' => 'Dolci',
                'description' => 'Prodotto da test',
                'price' => 3.50,
                'is_available' => '1',
                'is_active' => '1',
            ])
            ->assertRedirect(route('admin.products.index'));

        $product = Product::where('slug', 'maritozzo-crema')->firstOrFail();
        $editUrl = route('admin.products.edit', $product);

        $this->assertStringNotContainsString('/edit', $editUrl);
        $this->assertStringContainsString('/scheda', $editUrl);

        $this->actingAs($user)
            ->put(route('admin.products.update', $product), [
                'name' => 'Maritozzo panna',
                'category' => 'Dolci',
                'description' => 'Prodotto aggiornato',
                'price' => 3.80,
                'is_available' => '1',
                'is_active' => '1',
            ])
            ->assertRedirect(route('admin.products.index'));

        $this->assertDatabaseHas('products', [
            'slug' => 'maritozzo-panna',
            'price' => 3.80,
        ]);

        $this->actingAs($user)
            ->delete(route('admin.products.destroy', $product->fresh()))
            ->assertRedirect(route('admin.products.index'));

        $this->assertDatabaseMissing('products', ['slug' => 'maritozzo-panna']);
    }

    public function test_admin_can_upload_product_image_and_api_returns_public_url(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('admin.products.store'), [
                'name' => 'Torta immagine',
                'category' => 'Dolci',
                'description' => 'Prodotto con immagine',
                'price' => 6.50,
                'is_available' => '1',
                'is_active' => '1',
                'image' => UploadedFile::fake()->image('torta.jpg', 640, 480),
            ])
            ->assertRedirect(route('admin.products.index'));

        $product = Product::where('slug', 'torta-immagine')->firstOrFail();

        Storage::disk('public')->assertExists($product->image_path);
        $this->assertStringContainsString('/storage/products/', $product->image_url);

        $this->getJson(route('api.products.show', $product))
            ->assertOk()
            ->assertJsonPath('product.image_url', $product->image_url);
    }

    public function test_product_api_uses_placeholder_when_stored_image_is_missing(): void
    {
        Storage::fake('public');

        $product = Product::factory()->create([
            'slug' => 'immagine-mancante',
            'image_path' => 'products/missing.jpg',
            'is_active' => true,
        ]);

        $this->getJson(route('api.products.show', $product))
            ->assertOk()
            ->assertJsonPath('product.image_url', asset('images/rya-product-placeholder.svg'));
    }

    public function test_admin_can_accept_and_cancel_orders(): void
    {
        $user = User::factory()->create();
        $order = Order::create([
            'slug' => 'ordine-azioni',
            'customer_name' => 'Cliente Stato',
            'table_number' => 5,
            'status' => Order::STATUS_RECEIVED,
            'total_price' => 2.20,
        ]);

        $this->actingAs($user)
            ->patch(route('admin.orders.accept', $order))
            ->assertSessionHas('success');

        $this->assertSame(Order::STATUS_PENDING, $order->fresh()->status);

        $this->actingAs($user)
            ->patch(route('admin.orders.cancel', $order))
            ->assertSessionHas('success');

        $this->assertSame(Order::STATUS_CANCELLED, $order->fresh()->status);
        $this->assertDatabaseHas('order_histories', [
            'order_id' => $order->id,
            'reason' => OrderHistory::REASON_CANCELLED,
            'restored_at' => null,
        ]);
    }

    public function test_admin_cannot_complete_received_order_before_accepting_it(): void
    {
        $user = User::factory()->create();
        $order = Order::create([
            'slug' => 'ordine-non-accettato',
            'customer_name' => 'Cliente Stato',
            'table_number' => 5,
            'status' => Order::STATUS_RECEIVED,
            'total_price' => 2.20,
        ]);

        $this->actingAs($user)
            ->patch(route('admin.orders.complete', $order))
            ->assertSessionHasErrors('order');

        $this->assertSame(Order::STATUS_RECEIVED, $order->fresh()->status);
        $this->assertNull($order->fresh()->delivered_at);
    }

    public function test_admin_order_update_rejects_more_than_20_units_per_product(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['slug' => 'toast-test']);
        $order = Order::create([
            'slug' => 'ordine-quantita-admin',
            'customer_name' => 'Cliente Quantita',
            'table_number' => 5,
            'status' => Order::STATUS_RECEIVED,
            'total_price' => 2.20,
        ]);

        $this->actingAs($user)
            ->put(route('admin.orders.update', $order), [
                'customer_name' => 'Cliente Quantita',
                'table_number' => 5,
                'status' => Order::STATUS_RECEIVED,
                'items' => [
                    ['product_slug' => $product->slug, 'quantity' => 21],
                ],
            ])
            ->assertSessionHasErrors('items.0.quantity');
    }

    public function test_admin_can_restore_cancelled_order_within_30_minutes(): void
    {
        $user = User::factory()->create();
        $order = Order::create([
            'slug' => 'ordine-ripristino',
            'customer_name' => 'Cliente Restore',
            'table_number' => 3,
            'status' => Order::STATUS_CANCELLED,
            'total_price' => 4.40,
            'cancelled_at' => now(),
        ]);

        $order->histories()->create([
            'reason' => OrderHistory::REASON_CANCELLED,
            'archived_at' => now(),
            'restorable_until' => now()->addMinutes(30),
        ]);

        $this->actingAs($user)
            ->patch(route('admin.order-history.restore', $order))
            ->assertRedirect(route('admin.orders.index'));

        $this->assertSame(Order::STATUS_RECEIVED, $order->fresh()->status);
        $this->assertNotNull($order->histories()->first()->restored_at);
    }

    public function test_admin_can_complete_order_and_it_is_archived_immediately(): void
    {
        $user = User::factory()->create();
        $order = Order::create([
            'slug' => 'ordine-consegna',
            'customer_name' => 'Cliente Delivery',
            'table_number' => 8,
            'status' => Order::STATUS_PENDING,
            'total_price' => 5.00,
        ]);

        $this->actingAs($user)
            ->patch(route('admin.orders.complete', $order))
            ->assertRedirect(route('admin.order-history.index'))
            ->assertSessionHas('success');

        $this->assertSame(Order::STATUS_DELIVERED, $order->fresh()->status);

        $this->assertDatabaseHas('order_histories', [
            'order_id' => $order->id,
            'reason' => OrderHistory::REASON_DELIVERED,
        ]);
    }
}
