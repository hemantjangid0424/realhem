<?php

use App\Models\City;
use App\Models\Country;
use App\Models\Locality;
use App\Models\Property;
use App\Models\State;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->country = Country::create([
        'id' => 101,
        'name' => 'India',
        'iso2' => 'IN',
    ]);

    $this->state = State::create([
        'id' => 4026,
        'country_id' => $this->country->id,
        'name' => 'Gujarat',
        'state_code' => 'GJ',
    ]);

    $this->city = City::create([
        'state_id' => $this->state->id,
        'country_id' => $this->country->id,
        'name' => 'Ahmedabad',
        'is_metro' => true,
    ]);

    $this->locality = Locality::create([
        'city_id' => $this->city->id,
        'name' => 'Tragad',
        'slug' => 'tragad',
        'pincode' => '382470',
    ]);

    $this->admin = User::factory()->create([
        'role' => 'admin',
        'email' => 'admin@realhem.com',
    ]);

    $this->regularUser = User::factory()->create([
        'role' => 'user',
        'email' => 'user@realhem.com',
    ]);
});

test('non-admin users cannot access admin properties endpoints', function () {
    Sanctum::actingAs($this->regularUser);

    $response = $this->getJson('/api/admin/properties');
    $response->assertStatus(403);
});

test('admin can retrieve paginated properties with counts', function () {
    Property::factory()->count(3)->create([
        'user_id' => $this->regularUser->id,
        'city' => 'Ahmedabad',
        'locality' => 'Tragad',
        'status' => 'pending',
    ]);

    Property::factory()->count(2)->create([
        'user_id' => $this->regularUser->id,
        'city' => 'Ahmedabad',
        'locality' => 'Tragad',
        'status' => 'active',
        'is_verified' => true,
    ]);

    Sanctum::actingAs($this->admin);

    $response = $this->getJson('/api/admin/properties');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'counts' => ['total', 'pending', 'active', 'rejected', 'inactive', 'verified', 'featured'],
            'properties' => ['data', 'total', 'current_page'],
        ]);

    expect($response->json('counts.total'))->toBe(5)
        ->and($response->json('counts.pending'))->toBe(3)
        ->and($response->json('counts.active'))->toBe(2);
});

test('admin can filter properties by status', function () {
    Property::factory()->create([
        'user_id' => $this->regularUser->id,
        'title' => 'Pending Villa',
        'city' => 'Ahmedabad',
        'status' => 'pending',
    ]);

    Property::factory()->create([
        'user_id' => $this->regularUser->id,
        'title' => 'Active Penthouse',
        'city' => 'Ahmedabad',
        'status' => 'active',
    ]);

    Sanctum::actingAs($this->admin);

    $response = $this->getJson('/api/admin/properties?status=pending');

    $response->assertStatus(200);
    $data = $response->json('properties.data');
    expect(count($data))->toBe(1)
        ->and($data[0]['title'])->toBe('Pending Villa');
});

test('admin can view single property details', function () {
    $property = Property::factory()->create([
        'user_id' => $this->regularUser->id,
        'title' => 'Luxury 3BHK Residency',
        'city' => 'Ahmedabad',
    ]);

    Sanctum::actingAs($this->admin);

    $response = $this->getJson("/api/admin/properties/{$property->id}");

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'property' => [
                'id' => $property->id,
                'title' => 'Luxury 3BHK Residency',
            ],
        ]);
});

test('admin can approve a property', function () {
    $property = Property::factory()->create([
        'user_id' => $this->regularUser->id,
        'status' => 'pending',
        'is_verified' => false,
    ]);

    Sanctum::actingAs($this->admin);

    $response = $this->postJson("/api/admin/properties/{$property->id}/approve", [
        'verify' => true,
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
        ]);

    $property->refresh();
    expect($property->status)->toBe('active')
        ->and($property->is_verified)->toBeTrue();
});

test('admin can reject a property', function () {
    $property = Property::factory()->create([
        'user_id' => $this->regularUser->id,
        'status' => 'pending',
    ]);

    Sanctum::actingAs($this->admin);

    $response = $this->postJson("/api/admin/properties/{$property->id}/reject");

    $response->assertStatus(200);

    $property->refresh();
    expect($property->status)->toBe('rejected');
});

test('admin can toggle verified and featured status', function () {
    $property = Property::factory()->create([
        'user_id' => $this->regularUser->id,
        'is_verified' => false,
        'is_featured' => false,
    ]);

    Sanctum::actingAs($this->admin);

    // Toggle Verified
    $this->patchJson("/api/admin/properties/{$property->id}/toggle-verified")->assertStatus(200);
    expect($property->fresh()->is_verified)->toBeTrue();

    $this->patchJson("/api/admin/properties/{$property->id}/toggle-verified")->assertStatus(200);
    expect($property->fresh()->is_verified)->toBeFalse();

    // Toggle Featured
    $this->patchJson("/api/admin/properties/{$property->id}/toggle-featured")->assertStatus(200);
    expect($property->fresh()->is_featured)->toBeTrue();
});

test('admin can update property details', function () {
    $property = Property::factory()->create([
        'user_id' => $this->regularUser->id,
        'title' => 'Old Title',
        'expected_price' => 5000000,
    ]);

    Sanctum::actingAs($this->admin);

    $response = $this->putJson("/api/admin/properties/{$property->id}", [
        'title' => 'Updated Admin Title',
        'expected_price' => 6500000,
    ]);

    $response->assertStatus(200);

    $property->refresh();
    expect($property->title)->toBe('Updated Admin Title')
        ->and($property->expected_price)->toBe(6500000);
});

test('admin can permanently delete a property', function () {
    $property = Property::factory()->create([
        'user_id' => $this->regularUser->id,
    ]);

    Sanctum::actingAs($this->admin);

    $response = $this->deleteJson("/api/admin/properties/{$property->id}");

    $response->assertStatus(200);
    expect(Property::find($property->id))->toBeNull();
});
