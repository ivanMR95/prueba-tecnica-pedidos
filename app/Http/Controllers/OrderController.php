<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OrderController extends Controller
{
    public function __construct(
        private OrderService $orderService
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $orders = $request->user()
            ->orders()
            ->latest()
            ->get();

        return OrderResource::collection($orders);
    }

    public function show(Order $order): OrderResource
    {
        $order->load('items.product');

        return new OrderResource($order);
    }


    public function store(StoreOrderRequest $request): JsonResponse
    {
        $data = $request->validated();

        $order = $this->orderService->create(
            $request->user(),
            $data['items']
        );

        return (new OrderResource($order))
            ->response()
            ->setStatusCode(201);
    }

    public function cancel(Order $order): JsonResponse
    {
        if ($order->status !== Order::STATUS_PENDING) {
            return response()->json([
                'message' => 'Solo se pueden cancelar pedidos pendientes.',
            ], 422);
        }

        $order->update([
            'status' => Order::STATUS_CANCELLED,
        ]);

        return (new OrderResource(
            $order->refresh()->load('items.product')
        ))
            ->additional([
                'message' => 'Pedido cancelado correctamente.',
            ])
            ->response();
    }
}
