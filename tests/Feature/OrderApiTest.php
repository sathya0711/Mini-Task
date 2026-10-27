<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\WithFaker; 
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_can_be_created_successfully(): void
    {
        $customer = Customer::factory()->create();

        $product = Product::factory()->create([
            'price' => 100,
            'tax_percentage' => 10,
            'stock_on_hand' => 10,
        ]);

        $response = $this->postJson('/api/orders', [
            'customer' => [
                'name' => $customer->name,
                'email' => $customer->email,
            ],
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                ],
            ],
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock_on_hand' => 8,
        ]);

        $this->assertDatabaseHas('orders', [
            'customer_id' => $customer->id,
            'subtotal' => 200,
            'tax_amount' => 20,
            'grand_total' => 220,
        ]);
    }

    public function test_order_cannot_be_created_with_insufficient_stock(): void
    {
        $customer = Customer::factory()->create();

        $product = Product::factory()->create([
            'price' => 100,
            'tax_percentage' => 10,
            'stock_on_hand' => 2,
        ]);

        $response = $this->postJson('/api/orders', [
            'customer' => [
                'name' => $customer->name,
                'email' => $customer->email,
            ],
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 5,
                ],
            ],
        ]);

        $response->assertStatus(422);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock_on_hand' => 2,
        ]);

        $this->assertDatabaseMissing('orders', [
            'customer_id' => $customer->id,
        ]);
    }
}
