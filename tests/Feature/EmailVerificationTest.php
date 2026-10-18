<?php

use App\Models\User;
use App\Notifications\InquiryConfirmationNotification;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;

uses(RefreshDatabase::class);

test('new user registration triggers email verification notification with null verified timestamp', function () {
    Notification::fake();

    $sessionToken = 'test-verification-session-123';
    Cache::put("verified_mobile_{$sessionToken}", '9876543210', now()->addMinutes(15));

    $response = $this->postJson('/api/auth/complete-profile', [
        'session_token' => $sessionToken,
        'name' => 'Kavita Patel',
        'email' => 'kavita@example.com',
        'role' => 'user',
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'status' => 'logged_in',
            'user' => [
                'name' => 'Kavita Patel',
                'email' => 'kavita@example.com',
                'email_verified' => false,
            ],
        ]);

    $user = User::where('email', 'kavita@example.com')->first();
    expect($user)->not->toBeNull();
    expect($user->email_verified_at)->toBeNull();
    expect($user->hasVerifiedEmail())->toBeFalse();

    Notification::assertSentTo($user, VerifyEmailNotification::class);
});

test('user can verify email using valid signed verification url', function () {
    $user = User::factory()->create([
        'email' => 'testuser@example.com',
        'email_verified_at' => null,
    ]);

    expect($user->hasVerifiedEmail())->toBeFalse();

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
        [
            'id' => $user->id,
            'hash' => sha1($user->getEmailForVerification()),
        ]
    );

    $response = $this->getJson($verificationUrl);

    $response->assertStatus(200)
        ->assertJson([
            'status' => 'verified',
        ]);

    $user->refresh();
    expect($user->hasVerifiedEmail())->toBeTrue();
    expect($user->email_verified_at)->not->toBeNull();
});

test('verification url with invalid or tampered hash is rejected', function () {
    $user = User::factory()->create([
        'email' => 'tampered@example.com',
        'email_verified_at' => null,
    ]);

    $invalidUrl = URL::temporarySignedRoute(
        'verification.verify',
        Carbon::now()->addMinutes(60),
        [
            'id' => $user->id,
            'hash' => 'invalid-hash-value',
        ]
    );

    $response = $this->getJson($invalidUrl);

    $response->assertStatus(403);
    $user->refresh();
    expect($user->hasVerifiedEmail())->toBeFalse();
});

test('authenticated user can resend email verification notification', function () {
    Notification::fake();

    $user = User::factory()->create([
        'email' => 'unverified@example.com',
        'email_verified_at' => null,
    ]);

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/auth/email/resend');

    $response->assertStatus(200)
        ->assertJson([
            'status' => 'sent',
        ]);

    Notification::assertSentTo($user, VerifyEmailNotification::class);
});

test('already verified user resending verification returns already_verified status', function () {
    Notification::fake();

    $user = User::factory()->create([
        'email' => 'verified@example.com',
        'email_verified_at' => now(),
    ]);

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/auth/email/resend');

    $response->assertStatus(200)
        ->assertJson([
            'status' => 'already_verified',
        ]);

    Notification::assertNothingSent();
});

test('property inquiry reservation sends confirmation notification to inquiry email', function () {
    Notification::fake();

    $response = $this->postJson('/api/inquiries', [
        'type' => 'request_info',
        'name' => 'Rajesh Gupta',
        'email' => 'rajesh.gupta@example.com',
        'phone' => '+91 9988776655',
        'message' => 'I would like to reserve a visit for this 3BHK flat.',
    ]);

    $response->assertStatus(201)
        ->assertJson([
            'success' => true,
        ]);

    Notification::assertSentOnDemand(InquiryConfirmationNotification::class, function ($notification, $channels, $notifiable) {
        return $notifiable->routes['mail'] === 'rajesh.gupta@example.com';
    });
});
