<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;

class OrderController extends Controller
{
    public function __construct(
        private OrderService $orderService
    ) {}

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
}
