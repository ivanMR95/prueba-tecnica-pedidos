<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use Illuminate\Validation\ValidationException;

class DecreaseProductStock
{
    /**
     * Handle the event.
     */
    public function handle(OrderCreated $event): void
    {
        $order = $event->order->loadMissing('items.product');

        foreach ($order->items as $item) {
            $product = $item->product;

            if ($product->stock < $item->quantity) {
                throw ValidationException::withMessages([
                    'items' => [
                        "No hay stock suficiente para el producto {$product->name}.",
                    ],
                ]);
            }

            $product->decrement('stock', $item->quantity);
        }
    }
}
