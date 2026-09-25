<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_successful_checkout_flow(): void
    {
        // Mock products-service HTTP call
        Http::fake([
            '*/api/products/*/stock' => Http::response([
                'message' => 'Stock updated successfully',
                'data' => ['id' => 1, 'stock' => 8],
            ], 200),
        ]);

        $response = $this->postJson('/api/payments/checkout', [
            'user_id' => 1,
            'payment_method' => 'credit_card',
            'items' => [
                [
                    'product_id' => 1,
                    'quantity' => 2,
                    'unit_price' => 150.00,
                ],
            ],
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.order.status', 'paid')
            ->assertJsonPath('data.order.total', 300)
            ->assertJsonPath('data.payment.status', 'success');

        $this->assertDatabaseHas('orders', [
            'user_id' => 1,
            'total' => 300,
            'status' => 'paid',
        ]);

        $this->assertDatabaseHas('payments', [
            'status' => 'success',
            'amount' => 300,
        ]);
    }

    public function test_checkout_fails_when_product_service_fails(): void
    {
        // Mock products-service returning 422 insufficient stock
        Http::fake([
            '*/api/products/*/stock' => Http::response([
                'message' => 'Insufficient stock',
            ], 422),
        ]);

        $response = $this->postJson('/api/payments/checkout', [
            'user_id' => 1,
            'payment_method' => 'credit_card',
            'items' => [
                [
                    'product_id' => 99,
                    'quantity' => 50,
                    'unit_price' => 100.00,
                ],
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => ['checkout'],
            ]);

        // Order transaction rolled back
        $this->assertDatabaseMissing('orders', [
            'status' => 'paid',
        ]);
    }
}
