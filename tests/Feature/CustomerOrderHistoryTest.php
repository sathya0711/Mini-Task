<?php

namespace Tests\Feature;

use App\Models\Customer;
use Illuminate\Foundation\Testing\WithFaker; 
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerOrderHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_order_history_endpoint_returns_customer_and_orders(): void
    {
        $customer = Customer::factory()->create([
            'name' => 'Test Customer',
            'email' => 'testcustomer@example.com',
        ]);

        $response = $this->getJson(
            '/api/customers/' . urlencode($customer->email) . '/orders'
        );

        $response->assertOk()
            ->assertJsonPath('customer.email', $customer->email)
            ->assertJsonPath('orders', []);
    }
}

