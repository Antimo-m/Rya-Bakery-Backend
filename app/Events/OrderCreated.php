<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderCreated implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public Order $order)
    {
        $this->order->loadMissing('items.product');
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            // TODO: switch to PrivateChannel('admin.orders') before production
            // once the admin WebSocket auth flow is wired to session/Sanctum.
            new Channel('orders'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'order.created';
    }

    public function broadcastWith(): array
    {
        return [
            'order' => [
                'id' => $this->order->id,
                'slug' => $this->order->slug,
                'customer_name' => $this->order->customer_name,
                'table_number' => $this->order->table_number,
                'status' => $this->order->status,
                'status_label' => $this->order->statusLabel(),
                'total_price' => (float) $this->order->total_price,
                'created_at' => $this->order->created_at?->toIso8601String(),
                'items' => $this->order->items->map(fn ($item): array => [
                    'id' => $item->id,
                    'quantity' => $item->quantity,
                    'unit_price' => (float) $item->unit_price,
                    'line_total' => (float) $item->line_total,
                    'product' => [
                        'id' => $item->product?->id,
                        'slug' => $item->product?->slug,
                        'name' => $item->product?->name,
                        'image_url' => $item->product?->image_url,
                    ],
                ])->all(),
            ],
        ];
    }
}
