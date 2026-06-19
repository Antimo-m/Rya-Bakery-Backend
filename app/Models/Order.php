<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['slug', 'customer_name', 'table_number', 'status', 'total_price', 'notes', 'accepted_at', 'cancelled_at', 'delivered_at'])]
class Order extends Model
{
    use HasFactory;

    public const STATUS_RECEIVED = 'received';

    public const STATUS_PENDING = 'pending';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_DELIVERED = 'delivered';

    public const MAX_PRODUCT_QUANTITY = 20;

    public const STATUSES = [
        self::STATUS_RECEIVED => 'Ricevuto',
        self::STATUS_PENDING => 'In preparazione',
        self::STATUS_CANCELLED => 'Annullato',
        self::STATUS_DELIVERED => 'Pronto / Completato',
    ];

    protected function casts(): array
    {
        return [
            'total_price' => 'decimal:2',
            'accepted_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function histories(): HasMany
    {
        return $this->hasMany(OrderHistory::class);
    }

    public function activeHistory(): HasOne
    {
        return $this->hasOne(OrderHistory::class)->whereNull('restored_at')->latestOfMany();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query
            ->whereDoesntHave('activeHistory')
            ->where(function (Builder $query): void {
                $query
                    ->whereNull('delivered_at')
                    ->orWhere('delivered_at', '>', now()->subMinutes(10));
            });
    }

    public function scopeAwaiting(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_RECEIVED);
    }

    public function recalculateTotal(): void
    {
        $this->forceFill([
            'total_price' => $this->items()->sum('line_total'),
        ])->save();
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public static function archiveDeliveredOrders(): void
    {
        self::query()
            ->where('status', self::STATUS_DELIVERED)
            ->whereNotNull('delivered_at')
            ->where('delivered_at', '<=', now()->subMinutes(10))
            ->whereDoesntHave('activeHistory')
            ->each(function (Order $order): void {
                $order->histories()->create([
                    'reason' => OrderHistory::REASON_DELIVERED,
                    'archived_at' => $order->delivered_at,
                ]);
            });
    }
}
