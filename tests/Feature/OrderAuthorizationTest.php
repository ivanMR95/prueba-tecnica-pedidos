<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OrderAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_view_another_users_order(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $order = $owner->orders()->create([
            'total' => 50,
            'status' => Order::STATUS_PENDING,
        ]);

        Sanctum::actingAs($otherUser);

        $response = $this->getJson("/api/orders/{$order->id}");

        $response
            ->assertForbidden()
            ->assertJson([
                'message' => 'No tienes permiso para acceder a este pedido.',
            ]);
    }
}
