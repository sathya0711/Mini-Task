<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_low_stock_products_can_be_retrieved(): void
    {
        Product::factory()->create([
            'stock_on_hand' => 5,
        ]);

        Product::factory()->create([
            'stock_on_hand' => 15,
        ]);

        Product::factory()->create([
            'stock_on_hand' => 35,
        ]);

        $response = $this->getJson('/api/products/low-stock?threshold=20');

        $response->assertOk()
            ->assertJsonPath('threshold', 20)
            ->assertJsonPath('count', 2);
    }
}
