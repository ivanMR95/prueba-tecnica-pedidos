<?php

namespace App\Services;

use App\Events\OrderCreated;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderService
{
    public function create(User $user, array $items): Order
    {
        return DB::transaction(function () use ($user, $items) {
            $productIds = collect($items)->pluck('product_id');

            $products = Product::whereIn('id', $productIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($items as $item) {
                $product = $products->get($item['product_id']);

                if (!$product) {
                    throw ValidationException::withMessages([
                        'items' => ['Uno de los productos seleccionados no existe.'],
                    ]);
                }

                if ($product->stock < $item['quantity']) {
                    throw ValidationException::withMessages([
                        'items' => [
                            "No hay stock suficiente para el producto {$product->name}.",
                        ],
                    ]);
                }
            }

            $order = $user->orders()->create([
                'total' => 0,
                'status' => Order::STATUS_PENDING,
            ]);

            foreach ($items as $item) {
                $product = $products->get($item['product_id']);

                $order->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $product->price,
                    'subtotal' => $product->price * $item['quantity'],
                ]);
            }

            OrderCreated::dispatch($order);

            return $order->load('items.product');
        });
    }
}
