<?php

use App\Models\Property;
use App\Models\User;
use App\Models\UserActivity;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('unauthenticated request to log user activity receives 401', function () {
    $response = $this->postJson('/api/user/activities', [
        'activity_type' => 'view_property',
    ]);

    $response->assertStatus(401);
});

test('authenticated user can log property view activity', function () {
    $user = User::factory()->create();
    $property = Property::factory()->create();

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/user/activities', [
        'activity_type' => 'view_property',
        'property_id' => $property->id,
        'metadata' => [
            'slug' => $property->slug,
            'title' => $property->title,
        ],
    ]);

    $response->assertStatus(201)
        ->assertJson([
            'status' => 'success',
            'activity' => [
                'user_id' => $user->id,
                'activity_type' => 'view_property',
                'property_id' => $property->id,
            ],
        ]);

    $this->assertDatabaseHas('user_activities', [
        'user_id' => $user->id,
        'property_id' => $property->id,
        'activity_type' => 'view_property',
    ]);
});

test('logging the same property view within 60 seconds debounces and refreshes the timestamp', function () {
    $user = User::factory()->create();
    $property = Property::factory()->create();

    $res1 = $this->actingAs($user, 'sanctum')->postJson('/api/user/activities', [
        'activity_type' => 'view_property',
        'property_id' => $property->id,
    ]);
    $res1->assertStatus(201);

    $res2 = $this->actingAs($user, 'sanctum')->postJson('/api/user/activities', [
        'activity_type' => 'view_property',
        'property_id' => $property->id,
    ]);
    $res2->assertStatus(200);

    expect(UserActivity::where('user_id', $user->id)->count())->toBe(1);
});

test('authenticated user can retrieve their activity history', function () {
    $user = User::factory()->create();
    $property = Property::factory()->create(['title' => 'Luxury Penthouse in Pune']);

    UserActivity::factory()->create([
        'user_id' => $user->id,
        'activity_type' => 'view_property',
        'property_id' => $property->id,
    ]);

    $response = $this->actingAs($user, 'sanctum')->getJson('/api/user/activities');

    $response->assertStatus(200)
        ->assertJsonStructure(['data', 'current_page', 'total']);

    expect($response->json('data'))->toHaveCount(1);
    expect($response->json('data.0.property.title'))->toBe('Luxury Penthouse in Pune');
});

test('guest activities can be synced upon login', function () {
    $user = User::factory()->create();
    $prop1 = Property::factory()->create();
    $prop2 = Property::factory()->create();

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/user/activities/sync', [
        'activities' => [
            [
                'activity_type' => 'view_property',
                'property_id' => $prop1->id,
                'metadata' => ['title' => $prop1->title],
            ],
            [
                'activity_type' => 'view_property',
                'property_id' => $prop2->id,
                'metadata' => ['title' => $prop2->title],
            ],
        ],
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'status' => 'success',
            'synced' => 2,
        ]);

    expect(UserActivity::where('user_id', $user->id)->count())->toBe(2);
});
