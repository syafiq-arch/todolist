<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('user can register successfully', function () {
    $payload = [
        'name' => 'Budi Santoso',
        'email' => 'budi@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ];

    $response = $this->postJson('/api/register', $payload);

    $response->assertStatus(201)
        ->assertJson([
            'message' => 'Register berhasil',
            'data' => [
                'name' => 'Budi Santoso',
                'email' => 'budi@example.com',
            ],
        ]);

    $this->assertDatabaseHas('users', [
        'email' => 'budi@example.com',
    ]);
});

test('user registration fails if password confirmation does not match', function () {
    $payload = [
        'name' => 'Budi Santoso',
        'email' => 'budi@example.com',
        'password' => 'password123',
        'password_confirmation' => 'different_password',
    ];

    $response = $this->postJson('/api/register', $payload);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['password']);
});

test('user registration fails on duplicate email', function () {
    User::factory()->create(['email' => 'budi@example.com']);

    $payload = [
        'name' => 'Budi Baru',
        'email' => 'budi@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ];

    $response = $this->postJson('/api/register', $payload);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

test('user can login successfully and receive a sanctum token', function () {
    $user = User::factory()->create([
        'email' => 'budi@example.com',
        'password' => bcrypt('password123'),
    ]);

    $response = $this->postJson('/api/login', [
        'email' => 'budi@example.com',
        'password' => 'password123',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'message',
            'data' => [
                'token',
                'user' => ['id', 'name', 'email'],
            ],
        ]);
});

test('user cannot login with incorrect password', function () {
    User::factory()->create([
        'email' => 'budi@example.com',
        'password' => bcrypt('password123'),
    ]);

    $response = $this->postJson('/api/login', [
        'email' => 'budi@example.com',
        'password' => 'wrongpassword',
    ]);

    $response->assertStatus(401)
        ->assertJson([
            'message' => 'Email atau password salah',
        ]);
});

test('authenticated user can view their profile', function () {
    $user = User::factory()->create([
        'name' => 'Budi Santoso',
        'email' => 'budi@example.com',
    ]);

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/profile');

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Data profil berhasil diambil',
            'data' => [
                'id' => $user->id,
                'name' => 'Budi Santoso',
                'email' => 'budi@example.com',
            ],
        ]);
});

test('unauthenticated user cannot access profile endpoint', function () {
    $response = $this->getJson('/api/profile');

    $response->assertStatus(401);
});

test('authenticated user can logout and revoke token', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test_token')->plainTextToken;

    $response = $this->withHeader('Authorization', 'Bearer ' . $token)
        ->postJson('/api/logout');

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Logout berhasil',
        ]);

    expect($user->tokens()->count())->toBe(0);
});
