<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'Student/Parent',
            'phone' => '+1234567890'
        ];

        $response = $this->postJson('/api/auth/register', $userData);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'message',
                    'user' => [
                        'id',
                        'name',
                        'email',
                        'role',
                        'phone',
                        'is_active'
                    ],
                    'token',
                    'token_type'
                ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'role' => 'Student/Parent'
        ]);
    }

    public function test_user_can_login()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'role' => 'Student/Parent'
        ]);

        $loginData = [
            'email' => 'test@example.com',
            'password' => 'password123'
        ];

        $response = $this->postJson('/api/auth/login', $loginData);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'message',
                    'user',
                    'token',
                    'token_type'
                ]);
    }

    public function test_user_cannot_login_with_invalid_credentials()
    {
        $loginData = [
            'email' => 'nonexistent@example.com',
            'password' => 'wrongpassword'
        ];

        $response = $this->postJson('/api/auth/login', $loginData);

        $response->assertStatus(401)
                ->assertJson([
                    'message' => 'Invalid credentials'
                ]);
    }

    public function test_authenticated_user_can_access_protected_route()
    {
        $user = User::factory()->create([
            'role' => 'Student/Parent'
        ]);

        $response = $this->actingAs($user, 'sanctum')
                        ->getJson('/api/user');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'user' => [
                        'id',
                        'name',
                        'email',
                        'role'
                    ]
                ]);
    }

    public function test_role_middleware_restricts_access()
    {
        $student = User::factory()->create([
            'role' => 'Student/Parent'
        ]);

        // Student should not access admin routes
        $response = $this->actingAs($student, 'sanctum')
                        ->getJson('/api/admin/users');

        $response->assertStatus(403);

        // Admin should access admin routes
        $admin = User::factory()->create([
            'role' => 'Admin'
        ]);

        $response = $this->actingAs($admin, 'sanctum')
                        ->getJson('/api/admin/users');

        $response->assertStatus(200);
    }
}