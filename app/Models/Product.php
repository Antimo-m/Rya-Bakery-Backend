<?php

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

#[Fillable(['name', 'slug', 'category', 'description', 'price', 'image_path', 'is_available', 'is_active', 'is_best_seller', 'is_new', 'is_freshly_baked'])]
class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_available' => 'boolean',
            'is_active' => 'boolean',
            'is_best_seller' => 'boolean',
            'is_new' => 'boolean',
            'is_freshly_baked' => 'boolean',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    protected function imageUrl(): Attribute
    {
        return Attribute::get(fn (): string => $this->image_path && Storage::disk('public')->exists($this->image_path)
            ? asset(Storage::url($this->image_path))
            : asset('images/rya-product-placeholder.svg'));
    }

    public function specialBadges(): array
    {
        return collect([
            $this->is_best_seller ? ['type' => 'best_seller', 'label' => 'Best Seller', 'icon' => 'sparkle'] : null,
            $this->is_new ? ['type' => 'new', 'label' => 'Novita', 'icon' => 'star'] : null,
            $this->is_freshly_baked ? ['type' => 'freshly_baked', 'label' => 'Appena sfornato', 'icon' => 'flame'] : null,
        ])->filter()->values()->all();
    }
}
