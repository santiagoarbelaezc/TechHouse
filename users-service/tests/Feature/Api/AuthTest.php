<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Santiago Dev',
            'email' => 'santiago@techhouse.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'address' => 'Calle 100 #15-20',
            'phone' => '+573001234567',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'user' => ['id', 'name', 'email', 'address', 'phone'],
                    'token',
                    'token_type',
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'santiago@techhouse.test',
        ]);
    }

    public function test_user_can_login(): void
    {
        User::create([
            'name' => 'Santiago Dev',
            'email' => 'santiago@techhouse.test',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'santiago@techhouse.test',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'user' => ['id', 'name', 'email'],
                    'token',
                    'token_type',
                ],
            ]);
    }
}
