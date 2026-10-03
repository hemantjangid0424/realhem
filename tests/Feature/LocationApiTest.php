<?php

use App\Models\City;
use App\Models\Country;
use App\Models\Locality;
use App\Models\State;
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
            'city' => 'Delhi NCR',
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
