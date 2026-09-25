<?php

namespace Tests\Feature\Api;

use App\Models\Conversation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AssistantTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_send_chat_message(): void
    {
        Http::fake([
            '*/api/products' => Http::response([
                'data' => [
                    ['id' => 1, 'name' => 'MacBook Pro', 'price' => 2500, 'stock' => 5],
                ],
            ], 200),
        ]);

        $response = $this->postJson('/api/assistant/chat', [
            'user_id' => 1,
            'message' => '¿Tienen laptops disponibles?',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'conversation_id',
                'user_message' => ['id', 'role', 'content'],
                'assistant_message' => ['id', 'role', 'content'],
            ]);

        $this->assertDatabaseHas('messages', [
            'role' => 'user',
            'content' => '¿Tienen laptops disponibles?',
        ]);

        $this->assertDatabaseHas('messages', [
            'role' => 'assistant',
        ]);
    }
}
