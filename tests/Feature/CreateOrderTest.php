<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CreateOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_an_order(): void
    {
        $product = Product::query()
            ->create([
                'name' => 'Keyboard',
                'stock' => 10,
                'price' => 50,
            ]);

        $response = $this->postJson('/api/orders', [
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                ],
            ],
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('orders', [
            'total' => 100,
        ]);

        $this->assertDatabaseHas('order_items', [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);
    }

    public function test_it_fails_when_stock_is_insufficient(): void
    {
        $product = Product::query()
            ->create([
                'name' => 'Mouse',
                'stock' => 1,
                'price' => 30,
            ]);

        $response = $this->postJson('/api/orders', [
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                ],
            ],
        ]);

        $response->assertStatus(400);
    }
}
