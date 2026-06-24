<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CancelOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_cancelled_order_cannot_be_cancelled_again(): void
    {
        $user = User::factory()->create();

        $order = $user->orders()->create([
            'total' => 50.00,
            'status' => Order::STATUS_PENDING,
        ]);

        Sanctum::actingAs($user);

        $firstResponse = $this->putJson(
            "/api/orders/{$order->id}/cancel"
        );

        $firstResponse
            ->assertOk()
            ->assertJsonPath('data.status', Order::STATUS_CANCELLED);

        $secondResponse = $this->putJson(
            "/api/orders/{$order->id}/cancel"
        );

        $secondResponse
            ->assertUnprocessable()
            ->assertJson([
                'message' => 'Solo se pueden cancelar pedidos pendientes.',
            ]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => Order::STATUS_CANCELLED,
        ]);
    }
}
