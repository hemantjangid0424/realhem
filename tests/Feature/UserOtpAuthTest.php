<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

test('user can request otp with valid 10-digit mobile number', function () {
    $response = $this->postJson('/api/auth/send-otp', [
        'mobile' => '9876543210',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'message',
            'mobile',
            'is_registered',
            'dev_otp',
        ])
        ->assertJson([
            'mobile' => '9876543210',
        ]);

    expect(Cache::get('otp_9876543210'))->not->toBeNull();
});

test('requesting otp fails with invalid mobile number', function () {
    $response = $this->postJson('/api/auth/send-otp', [
        'mobile' => '12345',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['mobile']);
});

test('existing registered user can verify otp and immediately log in', function () {
    $user = User::factory()->create([
        'name' => 'Amit Sharma',
        'email' => 'amit@example.com',
        'mobile' => '9876543210',
        'role' => 'user',
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
});

test('new user verifying otp receives requires_profile status with session token', function () {
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

test('new user can complete profile with name, email, and role', function () {
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

    $response = $this->actingAs($user, 'sanctum')->getJson('/api/auth/me');

    $response->assertStatus(200)
        ->assertJson([
            'user' => [
                'id' => $user->id,
                'mobile' => '9876543210',
            ],
        ]);

    $token = $user->createToken('test')->plainTextToken;

    $logoutResponse = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/auth/logout');

    $logoutResponse->assertStatus(200);
});

test('international user (e.g. UAE +971) can request otp and register', function () {
    $sendOtpResponse = $this->postJson('/api/auth/send-otp', [
        'country_code' => '+971',
        'mobile' => '501234567',
    ]);

    $sendOtpResponse->assertStatus(200)
        ->assertJson([
            'country_code' => '+971',
            'mobile' => '501234567',
            'full_mobile' => '+971 501234567',
            'is_registered' => false,
        ]);

    $otp = Cache::get('otp_+971_501234567');
    expect($otp)->not->toBeNull();

    $verifyResponse = $this->postJson('/api/auth/verify-otp', [
        'country_code' => '+971',
        'mobile' => '501234567',
        'otp' => $otp,
    ]);

    $verifyResponse->assertStatus(200)
        ->assertJson([
            'status' => 'requires_profile',
            'country_code' => '+971',
            'mobile' => '501234567',
        ]);

    $sessionToken = $verifyResponse->json('session_token');

    $completeResponse = $this->postJson('/api/auth/complete-profile', [
        'session_token' => $sessionToken,
        'name' => 'Fatima Al Mansoori',
        'email' => 'fatima@dubai-estate.ae',
        'role' => 'owner',
    ]);

    $completeResponse->assertStatus(200)
        ->assertJson([
            'status' => 'logged_in',
            'user' => [
                'name' => 'Fatima Al Mansoori',
                'email' => 'fatima@dubai-estate.ae',
                'country_code' => '+971',
                'mobile' => '501234567',
                'role' => 'owner',
            ],
        ]);

    $this->assertDatabaseHas('users', [
        'country_code' => '+971',
        'mobile' => '501234567',
        'email' => 'fatima@dubai-estate.ae',
    ]);
});

test('existing international user can log in with otp', function () {
    $user = User::factory()->create([
        'name' => 'John Smith',
        'email' => 'john.smith@gmail.com',
        'country_code' => '+1',
        'mobile' => '2025550143',
        'role' => 'user',
    ]);

    Cache::put('otp_+1_2025550143', '1234', now()->addMinutes(10));

    $response = $this->postJson('/api/auth/verify-otp', [
        'country_code' => '+1',
        'mobile' => '2025550143',
        'otp' => '1234',
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'status' => 'logged_in',
            'user' => [
                'id' => $user->id,
                'name' => 'John Smith',
                'country_code' => '+1',
                'mobile' => '2025550143',
            ],
        ]);
});

test('authenticated user can update their profile information', function () {
    $user = User::factory()->create([
        'name' => 'Original Name',
        'email' => 'original@example.com',
        'role' => 'user',
    ]);

    $response = $this->actingAs($user, 'sanctum')->putJson('/api/auth/profile', [
        'name' => 'Hemant Sharma',
        'email' => 'hemant.sharma@example.com',
        'role' => 'owner',
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'status' => 'success',
            'user' => [
                'name' => 'Hemant Sharma',
                'email' => 'hemant.sharma@example.com',
                'role' => 'owner',
            ],
        ]);

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => 'Hemant Sharma',
        'email' => 'hemant.sharma@example.com',
        'role' => 'owner',
    ]);
});
