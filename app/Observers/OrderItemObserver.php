<?php

namespace App\Observers;

use App\Models\Order;
use App\Models\OrderItem;

class OrderItemObserver
{
    /**
     * Handle the OrderItem "created" event.
     */
    public function created(OrderItem $orderItem): void
    {
        $this->recalculateOrderTotal($orderItem->order_id);
    }

    /**
     * Handle the OrderItem "updated" event.
     */
    public function updated(OrderItem $orderItem): void
    {
        $this->recalculateOrderTotal($orderItem->order_id);
    }

    /**
     * Handle the OrderItem "deleted" event.
     */
    public function deleted(OrderItem $orderItem): void
    {
        $this->recalculateOrderTotal($orderItem->order_id);
    }

    private function recalculateOrderTotal(int $orderId): void
    {
        $order = Order::find($orderId);

        if (!$order) {
            return;
        }

        $order->update([
            'total' => $order->items()->sum('subtotal'),
        ]);
    }
}
