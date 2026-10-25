<?php

use App\Models\City;
use App\Models\Country;
use App\Models\Locality;
use App\Models\Property;
use App\Models\State;
use App\Models\User;
use App\Services\GeocodingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

test('can retrieve list of cities', function () {
    $country = Country::create([
        'id' => 101,
        'name' => 'India',
        'iso2' => 'IN',
    ]);

    $state = State::create([
        'id' => 4026,
        'country_id' => $country->id,
        'name' => 'Karnataka',
        'state_code' => 'KA',
    ]);

    City::create([
        'state_id' => $state->id,
        'country_id' => $country->id,
        'name' => 'Bengaluru',
        'is_metro' => true,
    ]);

    $response = $this->getJson('/api/locations/cities');

    $response->assertStatus(200)
        ->assertJsonFragment(['name' => 'Bengaluru']);
});

test('can retrieve localities for a city', function () {
    $country = Country::create([
        'id' => 101,
        'name' => 'India',
        'iso2' => 'IN',
    ]);

    $state = State::create([
        'id' => 4022,
        'country_id' => $country->id,
        'name' => 'Uttar Pradesh',
        'state_code' => 'UP',
    ]);

    $city = City::create([
        'state_id' => $state->id,
        'country_id' => $country->id,
        'name' => 'Noida',
        'is_metro' => true,
    ]);

    Locality::create([
        'city_id' => $city->id,
        'name' => 'Sector 150',
        'slug' => 'sector-150',
        'pincode' => '201310',
        'avg_price_per_sqft' => 8900,
    ]);

    $response = $this->getJson("/api/locations/localities?city={$city->name}");

    $response->assertStatus(200)
        ->assertJsonFragment(['name' => 'Sector 150', 'pincode' => '201310']);
});

test('can search localities and cities for autocomplete', function () {
    $country = Country::create([
        'id' => 101,
        'name' => 'India',
        'iso2' => 'IN',
    ]);

    $state = State::create([
        'id' => 4026,
        'country_id' => $country->id,
        'name' => 'Karnataka',
    ]);

    $city = City::create([
        'state_id' => $state->id,
        'country_id' => $country->id,
        'name' => 'Bengaluru',
    ]);

    Locality::create([
        'city_id' => $city->id,
        'name' => 'Whitefield',
        'slug' => 'whitefield',
        'pincode' => '560066',
        'avg_price_per_sqft' => 9800,
    ]);

    $response = $this->getJson('/api/locations/search?q=white');

    $response->assertStatus(200)
        ->assertJsonFragment([
            'name' => 'Whitefield',
            'city' => 'Bengaluru',
            'type' => 'locality',
        ]);
});

test('detects location fallback when coordinates are missing', function () {
    $response = $this->getJson('/api/locations/detect');

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'city' => 'Ahmedabad',
        ]);
});

test('detects and normalizes city from coordinates using mock response', function () {
    Http::fake([
        'nominatim.openstreetmap.org/*' => Http::response([
            'address' => [
                'city' => 'Ahmedabad',
                'suburb' => 'SG Highway',
                'state' => 'Gujarat',
            ],
        ], 200),
    ]);

    $response = $this->getJson('/api/locations/detect?lat=23.0225&lng=72.5714');

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'city' => 'Ahmedabad',
            'locality' => 'SG Highway',
            'state' => 'Gujarat',
            'display' => 'SG Highway, Ahmedabad',
        ])
        ->assertJsonStructure([
            'nearby_localities',
        ]);
});

test('retrieves nearby localities for a city', function () {
    $response = $this->getJson('/api/locations/nearby?city=Ahmedabad');

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'city' => 'Ahmedabad',
            'radius_km' => 3,
        ])
        ->assertJsonStructure([
            'nearby_localities',
        ]);
});

test('search-all endpoint returns categorized results for cities, localities and projects', function () {
    $country = Country::create([
        'id' => 101,
        'name' => 'India',
        'iso2' => 'IN',
    ]);

    $state = State::create([
        'id' => 4026,
        'country_id' => $country->id,
        'name' => 'Gujarat',
    ]);

    $city = City::create([
        'state_id' => $state->id,
        'country_id' => $country->id,
        'name' => 'Ahmedabad',
        'is_metro' => true,
    ]);

    $locality = Locality::create([
        'city_id' => $city->id,
        'name' => 'Makarba',
        'slug' => 'makarba',
        'pincode' => '380051',
    ]);

    $user = User::factory()->create();
    Property::factory()->create([
        'user_id' => $user->id,
        'project_name' => 'Palash Homes',
        'city' => 'Ahmedabad',
        'locality' => 'Makarba',
        'pincode' => '380051',
    ]);

    // Search for project
    $resProj = $this->getJson('/api/locations/search-all?q=Palash');
    $resProj->assertStatus(200)
        ->assertJsonFragment([
            'name' => 'Palash Homes',
            'category' => 'apartment',
            'project_name' => 'Palash Homes',
            'locality' => 'Makarba',
            'city' => 'Ahmedabad',
        ]);

    // Search for locality
    $resLoc = $this->getJson('/api/locations/search-all?q=Makarba');
    $resLoc->assertStatus(200)
        ->assertJsonFragment([
            'name' => 'Makarba',
            'category' => 'locality',
            'locality' => 'Makarba',
            'city' => 'Ahmedabad',
        ]);

    // Search for city
    $resCity = $this->getJson('/api/locations/search-all?q=Ahmedabad');
    $resCity->assertStatus(200)
        ->assertJsonFragment([
            'name' => 'Ahmedabad',
            'category' => 'city',
            'city' => 'Ahmedabad',
        ]);
});

test('nearby endpoint calculates real distance and excludes distant localities', function () {
    $country = Country::create([
        'id' => 101,
        'name' => 'India',
        'iso2' => 'IN',
    ]);

    $state = State::create([
        'id' => 4026,
        'country_id' => $country->id,
        'name' => 'Gujarat',
    ]);

    $city = City::create([
        'state_id' => $state->id,
        'country_id' => $country->id,
        'name' => 'Ahmedabad',
        'is_metro' => true,
    ]);

    // Near locality: Tragad (0.5 km away)
    Locality::create([
        'city_id' => $city->id,
        'name' => 'Tragad',
        'slug' => 'tragad',
        'latitude' => 23.1215,
        'longitude' => 72.5654,
    ]);

    // Near locality: Chandkheda (1.6 km away)
    Locality::create([
        'city_id' => $city->id,
        'name' => 'Chandkheda',
        'slug' => 'chandkheda',
        'latitude' => 23.1118,
        'longitude' => 72.5833,
    ]);

    // Distant locality: Bopal (14.3 km away)
    Locality::create([
        'city_id' => $city->id,
        'name' => 'Bopal',
        'slug' => 'bopal',
        'latitude' => 23.0336,
        'longitude' => 72.4646,
    ]);

    // Distant locality: Prahlad Nagar (13.4 km away)
    Locality::create([
        'city_id' => $city->id,
        'name' => 'Prahlad Nagar',
        'slug' => 'prahlad-nagar',
        'latitude' => 23.0124,
        'longitude' => 72.5076,
    ]);

    // User coordinates from screenshot (Chandkheda area)
    $response = $this->getJson('/api/locations/nearby?city=Ahmedabad&lat=23.11874485&lng=72.56948852&radius=3');

    $response->assertStatus(200);
    $nearby = $response->json('nearby_localities');

    expect($nearby)->toContain('Tragad')
        ->and($nearby)->toContain('Chandkheda')
        ->and($nearby)->not->toContain('Bopal')
        ->and($nearby)->not->toContain('Prahlad Nagar')
        ->and($nearby)->not->toContain('Satellite')
        ->and($nearby)->not->toContain('SG Highway');
});

test('GeocodingService accurately computes Haversine distance and normalizes market names', function () {
    // Chandkheda (23.1118, 72.5833) to Tragad (23.1215, 72.5654) is ~2.1 km
    $dist = GeocodingService::distance(23.1118, 72.5833, 23.1215, 72.5654);
    expect($dist)->toBeGreaterThan(1.5)
        ->and($dist)->toBeLessThan(2.6);

    // Identical point should be 0.0
    expect(GeocodingService::distance(23.0, 72.0, 23.0, 72.0))->toEqual(0.0);

    // City normalization checks
    expect(GeocodingService::normalizeCityName('Ahmedabad City', 'Gujarat', 'Tragad'))->toBe('Ahmedabad')
        ->and(GeocodingService::normalizeCityName('Noida Suburb', 'Uttar Pradesh'))->toBe('Delhi NCR')
        ->and(GeocodingService::normalizeCityName('Bengaluru South', 'Karnataka'))->toBe('Bangalore')
        ->and(GeocodingService::normalizeCityName('Thane West', 'Maharashtra'))->toBe('Mumbai')
        ->and(GeocodingService::normalizeCityName('Pimpri Chinchwad', 'Maharashtra'))->toBe('Pune');
});
