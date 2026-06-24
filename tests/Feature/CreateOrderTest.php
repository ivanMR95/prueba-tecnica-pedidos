<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CreateOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_an_order(): void
    {
        $user = User::factory()->create();

        $firstProduct = Product::factory()->create([
            'price' => 10.00,
            'stock' => 10,
        ]);

        $secondProduct = Product::factory()->create([
            'price' => 25.50,
            'stock' => 5,
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/orders', [
            'items' => [
                [
                    'product_id' => $firstProduct->id,
                    'quantity' => 2,
                ],
                [
                    'product_id' => $secondProduct->id,
                    'quantity' => 1,
                ],
            ],
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.total', '45.50')
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonCount(2, 'data.items');

        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'total' => 45.50,
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('order_items', [
            'product_id' => $firstProduct->id,
            'quantity' => 2,
            'unit_price' => 10.00,
            'subtotal' => 20.00,
        ]);

        $this->assertDatabaseHas('order_items', [
            'product_id' => $secondProduct->id,
            'quantity' => 1,
            'unit_price' => 25.50,
            'subtotal' => 25.50,
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $firstProduct->id,
            'stock' => 8,
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $secondProduct->id,
            'stock' => 4,
        ]);
    }
}
