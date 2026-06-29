<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['order_id', 'reason', 'archived_at', 'restorable_until', 'restored_at'])]
class OrderHistory extends Model
{
    use HasFactory;

    public const REASON_CANCELLED = 'cancelled';
    public const REASON_DELIVERED = 'delivered';

    protected function casts(): array
    {
        return [
            'archived_at' => 'datetime',
            'restorable_until' => 'datetime',
            'restored_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function canRestore(): bool
    {
        return $this->reason === self::REASON_CANCELLED
            && $this->restored_at === null
            && $this->restorable_until !== null
            && now()->lessThanOrEqualTo($this->restorable_until);
    }

    public function reasonLabel(): string
    {
        return match ($this->reason) {
            self::REASON_CANCELLED => 'Annullato',
            self::REASON_DELIVERED => 'Pronto per il ritiro',
            default => $this->reason,
        };
    }
}
