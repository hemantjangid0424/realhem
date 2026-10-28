<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('unauthenticated user cannot upload avatar', function () {
    $response = $this->postJson('/api/auth/profile/avatar', [
        'avatar' => UploadedFile::fake()->image('avatar.jpg'),
    ]);

    $response->assertStatus(401);
});

test('unauthenticated user cannot delete avatar', function () {
    $response = $this->deleteJson('/api/auth/profile/avatar');

    $response->assertStatus(401);
});

test('authenticated user can upload a valid avatar image', function () {
    Storage::fake('public');

    $user = User::factory()->create([
        'name' => 'Rahul Sharma',
        'email' => 'rahul@example.com',
        'mobile' => '9876543210',
    ]);

    $file = UploadedFile::fake()->image('profile.jpg', 400, 400)->size(1024);

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/auth/profile/avatar', [
            'avatar' => $file,
        ]);

    $response->assertStatus(200)
        ->assertJson([
            'status' => 'success',
            'message' => 'Profile picture updated successfully.',
            'user' => [
                'id' => $user->id,
                'name' => 'Rahul Sharma',
                'email' => 'rahul@example.com',
            ],
        ])
        ->assertJsonStructure([
            'avatar',
            'user' => ['id', 'name', 'email', 'avatar'],
        ]);

    $user->refresh();
    expect($user->avatar)->not->toBeNull();
    expect($user->avatar)->toContain('/storage/avatars/avatar_user_'.$user->id.'_');

    $filename = basename($user->avatar);
    Storage::disk('public')->assertExists('avatars/'.$filename);
});

test('uploading avatar fails when file is not an allowed image format', function () {
    Storage::fake('public');

    $user = User::factory()->create();

    $file = UploadedFile::fake()->create('document.pdf', 500, 'application/pdf');

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/auth/profile/avatar', [
            'avatar' => $file,
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['avatar']);
});

test('uploading avatar fails when file exceeds 5MB size limit', function () {
    Storage::fake('public');

    $user = User::factory()->create();

    // 6MB is 6144 KB, exceeding 5120 KB limit
    $file = UploadedFile::fake()->image('large.png')->size(6144);

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/auth/profile/avatar', [
            'avatar' => $file,
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['avatar']);
});

test('uploading a new avatar replaces and removes the previous avatar file', function () {
    Storage::fake('public');

    $user = User::factory()->create();

    $firstFile = UploadedFile::fake()->image('first.jpg', 300, 300);

    $firstResponse = $this->actingAs($user, 'sanctum')
        ->postJson('/api/auth/profile/avatar', [
            'avatar' => $firstFile,
        ]);

    $firstResponse->assertStatus(200);
    $firstAvatarUrl = $user->fresh()->avatar;
    $firstFilename = basename($firstAvatarUrl);
    Storage::disk('public')->assertExists('avatars/'.$firstFilename);

    // Upload second avatar
    $secondFile = UploadedFile::fake()->image('second.png', 400, 400);

    $secondResponse = $this->actingAs($user, 'sanctum')
        ->postJson('/api/auth/profile/avatar', [
            'avatar' => $secondFile,
        ]);

    $secondResponse->assertStatus(200);
    $secondAvatarUrl = $user->fresh()->avatar;
    $secondFilename = basename($secondAvatarUrl);

    // Assert new avatar exists and old avatar was removed
    Storage::disk('public')->assertExists('avatars/'.$secondFilename);
    Storage::disk('public')->assertMissing('avatars/'.$firstFilename);
    expect($secondAvatarUrl)->not->toBe($firstAvatarUrl);
});

test('authenticated user can delete their avatar', function () {
    Storage::fake('public');

    $user = User::factory()->create();

    $file = UploadedFile::fake()->image('avatar.jpg');

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/auth/profile/avatar', [
            'avatar' => $file,
        ])
        ->assertStatus(200);

    $avatarUrl = $user->fresh()->avatar;
    $filename = basename($avatarUrl);
    Storage::disk('public')->assertExists('avatars/'.$filename);

    $deleteResponse = $this->actingAs($user, 'sanctum')
        ->deleteJson('/api/auth/profile/avatar');

    $deleteResponse->assertStatus(200)
        ->assertJson([
            'status' => 'success',
            'message' => 'Profile picture removed successfully.',
            'user' => [
                'avatar' => null,
            ],
        ]);

    $user->refresh();
    expect($user->avatar)->toBeNull();
    Storage::disk('public')->assertMissing('avatars/'.$filename);
});
