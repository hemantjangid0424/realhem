<?php

use App\Models\Otp;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

test('new user submitting mobile on step 1 is prompted to provide profile details', function () {
    $response = $this->postJson('/api/auth/send-otp', [
        'mobile' => '9876543210',
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'status' => 'requires_profile',
            'next_step' => 'profile',
            'is_registered' => false,
            'mobile' => '9876543210',
        ]);
});

test('requesting otp fails with invalid mobile number', function () {
    $response = $this->postJson('/api/auth/send-otp', [
        'mobile' => '12345',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['mobile']);
});

test('existing registered user requesting otp receives code on mobile and email', function () {
    $user = User::factory()->create([
        'name' => 'Amit Sharma',
        'email' => 'amit@example.com',
        'mobile' => '9876543210',
        'country_code' => '+91',
        'role' => 'user',
    ]);

    $response = $this->postJson('/api/auth/send-otp', [
        'mobile' => '9876543210',
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'status' => 'otp_sent',
            'next_step' => 'otp',
            'is_registered' => true,
            'mobile' => '9876543210',
        ])
        ->assertJsonStructure([
            'message',
            'mobile',
            'email_hint',
            'dev_otp',
        ]);

    expect(Cache::get('otp_9876543210'))->not->toBeNull();

    $this->assertDatabaseHas('otps', [
        'mobile' => '9876543210',
    ]);
});

test('new user submitting name, email, and role on step 2 receives otp on both channels', function () {
    $response = $this->postJson('/api/auth/send-otp', [
        'mobile' => '9876543210',
        'name' => 'Rahul Khanna',
        'email' => 'rahul@example.com',
        'role' => 'owner',
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'status' => 'otp_sent',
            'next_step' => 'otp',
            'is_registered' => false,
            'mobile' => '9876543210',
            'email_hint' => 'rahul@example.com',
        ])
        ->assertJsonStructure(['dev_otp']);

    expect(Cache::get('pending_reg_9876543210'))->toMatchArray([
        'name' => 'Rahul Khanna',
        'email' => 'rahul@example.com',
        'role' => 'owner',
    ]);

    $this->assertDatabaseHas('otps', [
        'mobile' => '9876543210',
    ]);
});

test('new user cannot register with an email already taken by another account', function () {
    User::factory()->create([
        'email' => 'existing@example.com',
        'mobile' => '9111111111',
    ]);

    $response = $this->postJson('/api/auth/send-otp', [
        'mobile' => '9222222222',
        'name' => 'New User',
        'email' => 'existing@example.com',
        'role' => 'user',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

test('new user verifying unified otp is automatically registered and logged in', function () {
    // Step 2: send otp with profile
    $this->postJson('/api/auth/send-otp', [
        'mobile' => '9876543210',
        'name' => 'Priya Sen',
        'email' => 'priya@example.com',
        'role' => 'agent',
    ]);

    $otp = Cache::get('otp_9876543210');

    // Step 3: verify otp
    $response = $this->postJson('/api/auth/verify-otp', [
        'mobile' => '9876543210',
        'otp' => $otp,
    ]);

    $response->assertStatus(201)
        ->assertJson([
            'status' => 'logged_in',
            'user' => [
                'name' => 'Priya Sen',
                'email' => 'priya@example.com',
                'mobile' => '9876543210',
                'role' => 'agent',
                'email_verified' => true,
            ],
        ])
        ->assertJsonStructure(['token', 'user']);

    $this->assertDatabaseHas('users', [
        'mobile' => '9876543210',
        'email' => 'priya@example.com',
        'role' => 'agent',
    ]);

    expect(User::where('mobile', '9876543210')->first()->email_verified_at)->not->toBeNull();
});

test('existing registered user can verify otp and immediately log in', function () {
    $user = User::factory()->create([
        'name' => 'Amit Sharma',
        'email' => 'amit@example.com',
        'mobile' => '9876543210',
        'role' => 'user',
    ]);

    Otp::create([
        'country_code' => '+91',
        'mobile' => '9876543210',
        'otp' => '1234',
        'expires_at' => now()->addMinutes(10),
    ]);

    Cache::put('otp_9876543210', '1234', now()->addMinutes(10));

    $response = $this->postJson('/api/auth/verify-otp', [
        'mobile' => '9876543210',
        'otp' => '1234',
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'status' => 'logged_in',
            'user' => [
                'id' => $user->id,
                'name' => 'Amit Sharma',
                'email' => 'amit@example.com',
                'mobile' => '9876543210',
            ],
        ])
        ->assertJsonStructure(['token', 'user']);

    expect(Otp::where('mobile', '9876543210')->first()->verified_at)->not->toBeNull();
});

test('legacy fallback: new user without pending profile receives requires_profile status with session token', function () {
    Cache::put('otp_9111222333', '1234', now()->addMinutes(10));

    $response = $this->postJson('/api/auth/verify-otp', [
        'mobile' => '9111222333',
        'otp' => '1234',
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'status' => 'requires_profile',
            'mobile' => '9111222333',
        ])
        ->assertJsonStructure(['session_token', 'mobile']);
});

test('legacy complete profile endpoint continues to work', function () {
    $sessionToken = 'test-session-token-12345';
    Cache::put("verified_mobile_{$sessionToken}", '9111222333', now()->addMinutes(15));

    $response = $this->postJson('/api/auth/complete-profile', [
        'session_token' => $sessionToken,
        'name' => 'Hemant Verma',
        'email' => 'hemant@example.com',
        'role' => 'owner',
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'status' => 'logged_in',
            'user' => [
                'name' => 'Hemant Verma',
                'email' => 'hemant@example.com',
                'mobile' => '9111222333',
                'role' => 'owner',
            ],
        ])
        ->assertJsonStructure(['token', 'user']);

    $this->assertDatabaseHas('users', [
        'mobile' => '9111222333',
        'email' => 'hemant@example.com',
        'role' => 'owner',
    ]);
});

test('invalid otp is rejected with 422 error', function () {
    Cache::put('otp_9876543210', '5678', now()->addMinutes(10));

    $response = $this->postJson('/api/auth/verify-otp', [
        'mobile' => '9876543210',
        'otp' => '9999',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['otp']);
});

test('authenticated user can fetch me and logout', function () {
    $user = User::factory()->create([
        'mobile' => '9876543210',
    ]);

    $token = $user->createToken('test-token')->plainTextToken;

    $meResponse = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/auth/me');

    $meResponse->assertStatus(200)
        ->assertJson([
            'user' => [
                'id' => $user->id,
                'mobile' => '9876543210',
            ],
        ]);

    $logoutResponse = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/auth/logout');

    $logoutResponse->assertStatus(200)
        ->assertJson([
            'message' => 'Logged out successfully.',
        ]);
});
