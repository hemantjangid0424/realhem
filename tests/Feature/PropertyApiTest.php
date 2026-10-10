<?php

use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('unauthenticated user cannot post a property and receives 401', function () {
    $response = $this->postJson('/api/properties', [
        'property_for' => 'Sell',
        'property_type' => 'Residential Apartment',
        'city' => 'Ahmedabad',
        'locality' => 'Bopal',
        'bedrooms' => 3,
        'bathrooms' => 3,
        'carpet_area' => 1500,
        'furnishing_status' => 'Semi-Furnished',
        'construction_status' => 'Ready to Move',
        'expected_price' => 8500000,
    ]);

    $response->assertStatus(401);
});

test('authenticated user can post a property and it is stored in database', function () {
    $user = User::factory()->create([
        'name' => 'Rajesh Patel',
        'email' => 'rajesh@example.com',
        'mobile' => '9825012345',
        'country_code' => '+91',
        'role' => 'owner',
    ]);

    $payload = [
        'user_type' => 'Owner',
        'property_for' => 'Sell',
        'property_type' => 'Residential Apartment',
        'project_name' => 'Sun Real Homes',
        'city' => 'Ahmedabad',
        'locality' => 'Prahlad Nagar',
        'sub_locality' => 'Near Anandnagar Road',
        'landmark' => 'Opposite Titanium Square',
        'bedrooms' => 3,
        'bathrooms' => 3,
        'balconies' => 2,
        'carpet_area' => 1650,
        'super_builtup_area' => 2100,
        'furnishing_status' => 'Semi-Furnished',
        'floor_no' => '7',
        'total_floors' => 14,
        'facing' => 'East',
        'construction_status' => 'Ready to Move',
        'expected_price' => 12500000,
        'maintenance_charge' => 3500,
        'price_negotiable' => true,
        'amenities' => ['Lift', 'Gym', '24x7 Security', 'Swimming Pool', 'Reserved Parking'],
        'photos' => ['https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?w=800'],
        'description' => 'Bright and airy luxury 3 BHK apartment with open views.',
    ];

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/properties', $payload);

    $response->assertStatus(201)
        ->assertJson([
            'status' => 'success',
            'property' => [
                'city' => 'Ahmedabad',
                'locality' => 'Prahlad Nagar',
                'bedrooms' => 3,
                'expected_price' => 12500000,
                'user_id' => $user->id,
            ],
        ]);

    $this->assertDatabaseHas('properties', [
        'user_id' => $user->id,
        'city' => 'Ahmedabad',
        'locality' => 'Prahlad Nagar',
        'expected_price' => 12500000,
        'bedrooms' => 3,
    ]);
});

test('public users can browse properties with filters', function () {
    $user = User::factory()->create();

    Property::factory()->create([
        'user_id' => $user->id,
        'city' => 'Ahmedabad',
        'locality' => 'Bodakdev',
        'property_for' => 'Sell',
        'bedrooms' => 3,
        'expected_price' => 15000000,
        'is_verified' => true,
    ]);

    Property::factory()->create([
        'user_id' => $user->id,
        'city' => 'Pune',
        'locality' => 'Baner',
        'property_for' => 'Rent',
        'bedrooms' => 2,
        'expected_price' => 30000,
        'is_verified' => true,
    ]);

    $response = $this->getJson('/api/properties?city=Ahmedabad&type=buy');

    $response->assertStatus(200);
    expect($response->json('data'))->toHaveCount(1);
    expect($response->json('data.0.locality'))->toBe('Bodakdev');
});

test('authenticated user can view their posted properties', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    Property::factory()->count(2)->create(['user_id' => $user->id]);
    Property::factory()->create(['user_id' => $otherUser->id]);

    $response = $this->actingAs($user, 'sanctum')->getJson('/api/user/properties');

    $response->assertStatus(200);
    expect($response->json('data'))->toHaveCount(2);
});

test('property owner can update their property listing', function () {
    $user = User::factory()->create();
    $property = Property::factory()->create([
        'user_id' => $user->id,
        'title' => 'Original Title',
        'expected_price' => 5000000,
        'carpet_area' => 1000,
    ]);

    $response = $this->actingAs($user, 'sanctum')->putJson("/api/properties/{$property->id}", [
        'title' => 'Updated Beautiful 2 BHK Apartment',
        'property_for' => 'Sell',
        'property_type' => 'Residential Apartment',
        'city' => 'Ahmedabad',
        'locality' => 'Bopal',
        'bedrooms' => 2,
        'bathrooms' => 2,
        'carpet_area' => 1000,
        'furnishing_status' => 'Furnished',
        'construction_status' => 'Ready to Move',
        'expected_price' => 5500000,
        'price_negotiable' => true,
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'status' => 'success',
            'property' => [
                'title' => 'Updated Beautiful 2 BHK Apartment',
                'expected_price' => 5500000,
            ],
        ]);

    $this->assertDatabaseHas('properties', [
        'id' => $property->id,
        'title' => 'Updated Beautiful 2 BHK Apartment',
        'expected_price' => 5500000,
    ]);
});

test('non-owner cannot update someone else property', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $property = Property::factory()->create([
        'user_id' => $owner->id,
    ]);

    $response = $this->actingAs($intruder, 'sanctum')->putJson("/api/properties/{$property->id}", [
        'title' => 'Hacked title',
        'property_for' => 'Sell',
        'property_type' => 'Residential Apartment',
        'city' => 'Ahmedabad',
        'locality' => 'Bopal',
        'bedrooms' => 2,
        'bathrooms' => 2,
        'carpet_area' => 1000,
        'furnishing_status' => 'Furnished',
        'construction_status' => 'Ready to Move',
        'expected_price' => 5500000,
    ]);

    $response->assertStatus(403);
});

test('property owner can toggle status to sold or rented', function () {
    $user = User::factory()->create();
    $property = Property::factory()->create([
        'user_id' => $user->id,
        'status' => 'active',
    ]);

    $response = $this->actingAs($user, 'sanctum')->patchJson("/api/properties/{$property->id}/status", [
        'status' => 'sold',
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'status' => 'success',
            'property' => [
                'status' => 'sold',
            ],
        ]);

    $this->assertDatabaseHas('properties', [
        'id' => $property->id,
        'status' => 'sold',
    ]);
});

test('property owner can delete their property listing', function () {
    $user = User::factory()->create();
    $property = Property::factory()->create([
        'user_id' => $user->id,
    ]);

    $response = $this->actingAs($user, 'sanctum')->deleteJson("/api/properties/{$property->id}");

    $response->assertStatus(200);
    $this->assertDatabaseMissing('properties', ['id' => $property->id]);
});

test('commercial properties are returned when filtering by type=commercial and keyword', function () {
    $user = User::factory()->create();

    $commercial = Property::factory()->create([
        'user_id' => $user->id,
        'property_for' => 'Sell',
        'property_type' => 'Commercial Shop',
        'project_name' => 'Palash Homes',
        'title' => '1200 sq.ft. Semi-Furnished Commercial Shop for Sale in Palash Homes, Makarba, Ahmedabad',
        'locality' => 'Makarba',
        'city' => 'Ahmedabad',
        'expected_price' => 7740000,
        'status' => 'active',
    ]);

    $residential = Property::factory()->create([
        'user_id' => $user->id,
        'property_for' => 'Sell',
        'property_type' => 'Residential Apartment',
        'project_name' => 'Palash Homes Residential',
        'title' => '2 BHK Residential Apartment in Palash Homes',
        'locality' => 'Makarba',
        'city' => 'Ahmedabad',
        'expected_price' => 5500000,
        'status' => 'active',
    ]);

    $response = $this->getJson('/api/properties?type=commercial&city=Ahmedabad&keyword=Palash+Homes');

    $response->assertStatus(200)
        ->assertJsonFragment(['id' => $commercial->id, 'property_type' => 'Commercial Shop'])
        ->assertJsonMissing(['id' => $residential->id, 'property_type' => 'Residential Apartment']);
});
