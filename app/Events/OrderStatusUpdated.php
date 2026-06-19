<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderStatusUpdated implements ShouldBroadcastNow
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
            new Channel('orders.'.$this->order->slug),
        ];
    }

    public function broadcastAs(): string
    {
        return 'order.status.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'order' => [
                'slug' => $this->order->slug,
                'customer_name' => $this->order->customer_name,
                'table_number' => $this->order->table_number,
                'status' => $this->order->status,
                'status_label' => $this->order->statusLabel(),
                'total_price' => (float) $this->order->total_price,
                'created_at' => $this->order->created_at?->toIso8601String(),
                'items' => $this->order->items->map(fn ($item): array => [
                    'product_slug' => $item->product?->slug,
                    'product_name' => $item->product?->name,
                    'product_image_url' => $item->product?->image_url,
                    'quantity' => $item->quantity,
                    'unit_price' => (float) $item->unit_price,
                    'line_total' => (float) $item->line_total,
                ])->all(),
            ],
        ];
    }
}
