<?php

use App\Models\Property;
use App\Models\User;
use App\Notifications\ListingPublishedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

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

test('properties can be sorted by price low to high and high to low', function () {
    $user = User::factory()->create();

    $cheap = Property::factory()->create([
        'user_id' => $user->id,
        'city' => 'Ahmedabad',
        'expected_price' => 2500000,
        'status' => 'active',
    ]);

    $expensive = Property::factory()->create([
        'user_id' => $user->id,
        'city' => 'Ahmedabad',
        'expected_price' => 15000000,
        'status' => 'active',
    ]);

    $resLow = $this->getJson('/api/properties?city=Ahmedabad&sort=price_low');
    $resLow->assertStatus(200);
    $dataLow = $resLow->json('data');
    expect($dataLow[0]['id'])->toBe($cheap->id);

    $resHigh = $this->getJson('/api/properties?city=Ahmedabad&sort=price_high');
    $resHigh->assertStatus(200);
    $dataHigh = $resHigh->json('data');
    expect($dataHigh[0]['id'])->toBe($expensive->id);
});

test('properties support simple pagination without full count overhead', function () {
    $user = User::factory()->create();
    Property::factory()->count(5)->create([
        'user_id' => $user->id,
        'city' => 'Surat',
        'status' => 'active',
    ]);

    $response = $this->getJson('/api/properties?city=Surat&per_page=2&simple=1');
    $response->assertStatus(200)
        ->assertJsonStructure(['data', 'current_page', 'per_page', 'next_page_url']);

    expect(count($response->json('data')))->toBe(2);
});

test('property can be fetched by slug starting with a number without resolving to id 1', function () {
    $user = User::factory()->create();

    $prop1 = Property::factory()->create([
        'user_id' => $user->id,
        'title' => 'Property One in Ahmedabad',
        'city' => 'Ahmedabad',
        'slug' => 'property-one-ahmedabad',
    ]);

    $propTarget = Property::factory()->create([
        'user_id' => $user->id,
        'title' => '1 BHK Apartment in Wakad Pune',
        'city' => 'Pune',
        'locality' => 'Wakad',
        'slug' => '1-bhk-residential-apartment-for-sell-in-wakad-pune-spld-5inw-499967',
    ]);

    // Query by slug that begins with '1-'
    $response = $this->getJson("/api/property/{$propTarget->slug}");

    $response->assertStatus(200);
    expect($response->json('property.id'))->toBe($propTarget->id);
    expect($response->json('property.title'))->toBe('1 BHK Apartment in Wakad Pune');
    expect($response->json('property.city'))->toBe('Pune');

    // Query by numeric ID
    $responseById = $this->getJson("/api/property/{$prop1->id}");
    $responseById->assertStatus(200);
    expect($responseById->json('property.id'))->toBe($prop1->id);
    expect($responseById->json('property.title'))->toBe('Property One in Ahmedabad');
});

test('authenticated user can upload videos for property listing', function () {
    Storage::fake('public');

    $user = User::factory()->create();

    $video = UploadedFile::fake()->create('walkthrough.mp4', 5000, 'video/mp4');

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/properties/upload-videos', [
            'videos' => [$video],
        ]);

    $response->assertStatus(200)
        ->assertJsonStructure(['status', 'urls']);

    expect($response->json('status'))->toBe('success');
    expect(count($response->json('urls')))->toBe(1);
    expect($response->json('urls.0'))->toContain('/storage/properties/videos/');
});

test('authenticated user can post property with videos', function () {
    $user = User::factory()->create();

    $payload = [
        'user_type' => 'Owner',
        'property_for' => 'Sell',
        'property_type' => 'Residential Apartment',
        'city' => 'Ahmedabad',
        'locality' => 'Satellite',
        'bedrooms' => 3,
        'bathrooms' => 3,
        'carpet_area' => 1800,
        'expected_price' => 15000000,
        'photos' => ['https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?w=800'],
        'videos' => ['/storage/properties/videos/sample-tour.mp4'],
    ];

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/properties', $payload);

    $response->assertStatus(201);
    expect($response->json('property.videos'))->toBe(['/storage/properties/videos/sample-tour.mp4']);

    $propertyId = $response->json('property.id');
    $fetchResponse = $this->getJson("/api/property/{$propertyId}");
    $fetchResponse->assertStatus(200);
    expect($fetchResponse->json('property.videos'))->toBe(['/storage/properties/videos/sample-tour.mp4']);
});

test('authenticated user can upload photos and watermark is applied', function () {
    Storage::fake('public');

    $user = User::factory()->create();

    $photo = UploadedFile::fake()->image('living_room.jpg', 800, 600);

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/properties/upload-photos', [
            'photos' => [$photo],
        ]);

    $response->assertStatus(200)
        ->assertJsonStructure(['status', 'urls']);

    expect($response->json('status'))->toBe('success');
    expect(count($response->json('urls')))->toBe(1);
    expect($response->json('urls.0'))->toContain('/storage/properties/');

    // Assert file exists in fake storage
    $storedFiles = Storage::disk('public')->allFiles('properties');
    expect(count($storedFiles))->toBe(1);
});

test('public users can filter properties by furnishing, posted_by, bathrooms, facing, negotiable, and amenities', function () {
    $user = User::factory()->create();

    $match = Property::factory()->create([
        'user_id' => $user->id,
        'city' => 'Ahmedabad',
        'locality' => 'Prahlad Nagar',
        'property_for' => 'Sell',
        'furnishing_status' => 'Furnished',
        'user_type' => 'Owner',
        'bathrooms' => 3,
        'facing' => 'East',
        'price_negotiable' => true,
        'amenities' => ['Lift', 'Gym', 'Swimming Pool'],
        'expected_price' => 9000000,
    ]);

    $other = Property::factory()->create([
        'user_id' => $user->id,
        'city' => 'Ahmedabad',
        'locality' => 'Vastral',
        'property_for' => 'Sell',
        'furnishing_status' => 'Unfurnished',
        'user_type' => 'Agent',
        'bathrooms' => 1,
        'facing' => 'South',
        'price_negotiable' => false,
        'amenities' => ['Parking'],
        'expected_price' => 4000000,
    ]);

    // Filter by furnishing
    $resFurnished = $this->getJson('/api/properties?furnishing=Furnished');
    $resFurnished->assertStatus(200);
    $ids = collect($resFurnished->json('data'))->pluck('id');
    expect($ids)->toContain($match->id)->not->toContain($other->id);

    // Filter by posted_by
    $resOwner = $this->getJson('/api/properties?posted_by=Owner');
    $resOwner->assertStatus(200);
    $ids = collect($resOwner->json('data'))->pluck('id');
    expect($ids)->toContain($match->id)->not->toContain($other->id);

    // Filter by bathrooms
    $resBaths = $this->getJson('/api/properties?bathrooms=3');
    $resBaths->assertStatus(200);
    $ids = collect($resBaths->json('data'))->pluck('id');
    expect($ids)->toContain($match->id)->not->toContain($other->id);

    // Filter by facing
    $resFacing = $this->getJson('/api/properties?facing=East');
    $resFacing->assertStatus(200);
    $ids = collect($resFacing->json('data'))->pluck('id');
    expect($ids)->toContain($match->id)->not->toContain($other->id);

    // Filter by negotiable
    $resNego = $this->getJson('/api/properties?negotiable_only=1');
    $resNego->assertStatus(200);
    $ids = collect($resNego->json('data'))->pluck('id');
    expect($ids)->toContain($match->id)->not->toContain($other->id);

    // Filter by amenities
    $resAmen = $this->getJson('/api/properties?amenities=Gym,Swimming Pool');
    $resAmen->assertStatus(200);
    $ids = collect($resAmen->json('data'))->pluck('id');
    expect($ids)->toContain($match->id)->not->toContain($other->id);
});

test('posting a new property sends live listing notification email to authenticated user', function () {
    Notification::fake();

    $user = User::factory()->create([
        'name' => 'Kavita Joshi',
        'email' => 'kavita@example.com',
        'mobile' => '9876501234',
        'role' => 'owner',
    ]);

    $payload = [
        'user_type' => 'Owner',
        'property_for' => 'Sell',
        'property_type' => 'Residential Apartment',
        'title' => 'Stunning 2 BHK in Satellite',
        'city' => 'Ahmedabad',
        'locality' => 'Satellite',
        'bedrooms' => 2,
        'bathrooms' => 2,
        'carpet_area' => 1100,
        'furnishing_status' => 'Furnished',
        'construction_status' => 'Ready to Move',
        'expected_price' => 7500000,
    ];

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/properties', $payload);

    $response->assertStatus(201)
        ->assertJson([
            'status' => 'success',
            'message' => 'Property posted successfully! Your listing is now live on RealHem.',
        ]);

    $propertyId = $response->json('property.id');

    Notification::assertSentTo(
        $user,
        ListingPublishedNotification::class,
        function (ListingPublishedNotification $notification) use ($propertyId) {
            return $notification->property->id === $propertyId;
        }
    );
});
