<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_products(): void
    {
        $category = Category::create([
            'name' => 'Laptops',
            'slug' => 'laptops',
        ]);

        Product::create([
            'category_id' => $category->id,
            'name' => 'MacBook Pro 16',
            'slug' => 'macbook-pro-16',
            'description' => 'Apple M3 Pro',
            'price' => 2499.99,
            'stock' => 10,
        ]);

        $response = $this->getJson('/api/products');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'slug', 'price', 'stock'],
                ],
                'meta',
            ]);
    }

    public function test_can_create_product(): void
    {
        $category = Category::create([
            'name' => 'Monitors',
            'slug' => 'monitors',
        ]);

        $response = $this->postJson('/api/products', [
            'category_id' => $category->id,
            'name' => 'UltraWide 34 Monitor',
            'slug' => 'ultrawide-34-monitor',
            'description' => '144Hz curved gaming monitor',
            'price' => 599.99,
            'stock' => 15,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'UltraWide 34 Monitor')
            ->assertJsonPath('data.stock', 15);

        $this->assertDatabaseHas('products', [
            'slug' => 'ultrawide-34-monitor',
        ]);
    }

    public function test_internal_service_can_update_stock(): void
    {
        $category = Category::create([
            'name' => 'Keyboards',
            'slug' => 'keyboards',
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Mechanical Keyboard RGB',
            'slug' => 'mechanical-keyboard-rgb',
            'price' => 120.00,
            'stock' => 5,
        ]);

        // With valid service token
        $response = $this->withHeaders([
            'X-Service-Token' => config('services.internal_token'),
        ])->patchJson("/api/products/{$product->id}/stock", [
            'quantity' => 2,
            'action' => 'decrement',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.stock', 3);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock' => 3,
        ]);
    }
}
