<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('client spa renders successfully at root route', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
    $response->assertSee('client-app');
});

test('admin spa renders successfully at admin route', function () {
    $response = $this->get('/admin/login');

    $response->assertStatus(200);
    $response->assertSee('admin-app');
});

test('admin can login via api with valid credentials', function () {
    $admin = User::factory()->create([
        'email' => 'admin@realhem.com',
        'password' => Hash::make('password'),
        'role' => 'admin',
    ]);

    $response = $this->postJson('/api/admin/auth/login', [
        'email' => 'admin@realhem.com',
        'password' => 'password',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'message',
            'token',
            'user' => ['id', 'name', 'email', 'role'],
        ])
        ->assertJson([
            'user' => [
                'email' => 'admin@realhem.com',
                'role' => 'admin',
            ],
        ]);
});

test('non-admin user is denied admin api login', function () {
    $user = User::factory()->create([
        'email' => 'buyer@realhem.com',
        'password' => Hash::make('password'),
        'role' => 'user',
    ]);

    $response = $this->postJson('/api/admin/auth/login', [
        'email' => 'buyer@realhem.com',
        'password' => 'password',
    ]);

    $response->assertStatus(403)
        ->assertJson([
            'message' => 'Access denied. Administrative privileges are required.',
        ]);
});

test('authenticated admin can retrieve dashboard statistics', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $response = $this->actingAs($admin, 'sanctum')->getJson('/api/admin/dashboard/stats');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'stats' => [
                'total_users',
                'total_admins',
                'total_agents',
                'total_owners',
                'total_buyers',
                'total_properties',
            ],
            'recent_users',
        ]);
});

test('unauthenticated request to admin stats is rejected', function () {
    $response = $this->getJson('/api/admin/dashboard/stats');

    $response->assertStatus(401);
});
