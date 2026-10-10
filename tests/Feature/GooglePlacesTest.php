<?php

use App\Models\GooglePlace;
use App\Services\GooglePlacesService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

test('first search stores geocoded place in google_places database cache', function () {
    $results = GooglePlacesService::searchPlaces('Vastral', 'Ahmedabad');

    expect($results)->not->toBeEmpty();
    $first = $results[0];

    expect($first)->toHaveKeys(['name', 'display', 'latitude', 'longitude', 'pincode', 'cached'])
        ->and($first['pincode'])->toBe('382418');

    $this->assertDatabaseHas('google_places', [
        'city' => 'Ahmedabad',
        'pincode' => '382418',
        'hit_count' => 1,
    ]);
});

test('subsequent searches for the same place return cached result from database without external API call', function () {
    // Prime the cache
    GooglePlacesService::searchPlaces('Bopal', 'Ahmedabad');

    // Second call for the same query
    $cachedResults = GooglePlacesService::searchPlaces('Bopal', 'Ahmedabad');

    expect($cachedResults)->not->toBeEmpty()
        ->and($cachedResults[0]['cached'])->toBeTrue()
        ->and($cachedResults[0]['pincode'])->toBe('380058');

    $place = GooglePlace::where('city', 'Ahmedabad')->where('pincode', '380058')->first();
    expect($place->hit_count)->toBe(2);
});

test('google places API (New) response and place details are stored in database when API key is provided', function () {
    Config::set('services.google.places_api_key', 'AIzaSyFakeGoogleApiKey123');

    Http::fake([
        'places.googleapis.com/v1/places:autocomplete' => Http::response([
            'suggestions' => [
                [
                    'placePrediction' => [
                        'place' => 'places/ChIJ_12345Koramangala',
                        'placeId' => 'ChIJ_12345Koramangala',
                        'text' => [
                            'text' => 'Koramangala, Bengaluru, Karnataka, India',
                        ],
                        'structuredFormat' => [
                            'mainText' => ['text' => 'Koramangala'],
                            'secondaryText' => ['text' => 'Bengaluru, Karnataka, India'],
                        ],
                        'types' => ['sublocality_level_1', 'sublocality', 'political', 'geocode'],
                    ],
                ],
            ],
        ], 200),
        'places.googleapis.com/v1/places/ChIJ_12345Koramangala*' => Http::response([
            'id' => 'ChIJ_12345Koramangala',
            'displayName' => [
                'text' => 'Koramangala',
                'languageCode' => 'en',
            ],
            'formattedAddress' => 'Koramangala, Bengaluru, Karnataka 560034, India',
            'location' => [
                'latitude' => 12.9352,
                'longitude' => 77.6245,
            ],
            'addressComponents' => [
                ['longText' => 'Koramangala', 'shortText' => 'Koramangala', 'types' => ['sublocality_level_1', 'sublocality', 'political']],
                ['longText' => 'Bengaluru', 'shortText' => 'Bengaluru', 'types' => ['locality', 'political']],
                ['longText' => 'Karnataka', 'shortText' => 'KA', 'types' => ['administrative_area_level_1', 'political']],
                ['longText' => '560034', 'shortText' => '560034', 'types' => ['postal_code']],
            ],
            'types' => ['sublocality_level_1', 'sublocality', 'political', 'geocode'],
        ], 200),
    ]);

    $results = GooglePlacesService::searchPlaces('Koramangala', 'Bengaluru');

    expect($results)->not->toBeEmpty();
    $top = $results[0];

    expect($top['provider'])->toBe('google_places_new')
        ->and($top['pincode'])->toBe('560034')
        ->and($top['latitude'])->toBe(12.9352)
        ->and($top['longitude'])->toBe(77.6245)
        ->and($top['city'])->toBe('Bengaluru');

    $this->assertDatabaseHas('google_places', [
        'place_id' => 'ChIJ_12345Koramangala',
        'city' => 'Bengaluru',
        'pincode' => '560034',
        'provider' => 'google_places_new',
    ]);
});

test('location search api and property suggestions api return google places results', function () {
    GooglePlace::create([
        'place_id' => 'ChIJ_powai_test',
        'query_hash' => hash('sha256', 'powai mumbai india'),
        'raw_query' => 'Powai',
        'normalized_query' => 'powai mumbai india',
        'name' => 'Powai',
        'formatted_address' => 'Powai, Mumbai, Maharashtra 400076, India',
        'locality' => 'Powai',
        'city' => 'Mumbai',
        'state' => 'Maharashtra',
        'pincode' => '400076',
        'latitude' => 19.1176,
        'longitude' => 72.9060,
        'hit_count' => 1,
        'provider' => 'google_places_new',
    ]);

    $response = $this->getJson('/api/locations/search?q=Powai');
    $response->assertStatus(200)
        ->assertJsonFragment([
            'locality' => 'Powai',
            'city' => 'Mumbai',
        ]);

    $suggestionRes = $this->getJson('/api/search/suggestions?q=Powai');
    $suggestionRes->assertStatus(200)
        ->assertJsonFragment([
            'title' => 'Powai',
            'city' => 'Mumbai',
        ]);
});

test('flat and building name search returns project suggestion from google places and caches to database', function () {
    Config::set('services.google.places_api_key', 'AIzaSyFakeGoogleApiKey123');

    Http::fake([
        'places.googleapis.com/v1/places:autocomplete' => Http::response([
            'suggestions' => [
                [
                    'placePrediction' => [
                        'place' => 'places/ChIJ_KalashSquareChandkheda',
                        'placeId' => 'ChIJ_KalashSquareChandkheda',
                        'text' => [
                            'text' => 'Kalash Square Mansarovar Road, Tragad, Chandkheda, Ahmedabad, Gujarat 382424',
                        ],
                        'structuredFormat' => [
                            'mainText' => ['text' => 'Kalash Square'],
                            'secondaryText' => ['text' => 'Tragad, Chandkheda, Ahmedabad, Gujarat 382424'],
                        ],
                        'types' => ['premise', 'establishment', 'point_of_interest'],
                    ],
                ],
            ],
        ], 200),
        'places.googleapis.com/v1/places/ChIJ_KalashSquareChandkheda*' => Http::response([
            'id' => 'ChIJ_KalashSquareChandkheda',
            'displayName' => [
                'text' => 'Kalash Square',
                'languageCode' => 'en',
            ],
            'formattedAddress' => 'Kalash Square Mansarovar Road, Tragad, Chandkheda, Ahmedabad, Gujarat 382424, India',
            'location' => [
                'latitude' => 23.1198,
                'longitude' => 72.5855,
            ],
            'addressComponents' => [
                ['longText' => 'Kalash Square', 'shortText' => 'Kalash Square', 'types' => ['premise']],
                ['longText' => 'Tragad', 'shortText' => 'Tragad', 'types' => ['sublocality_level_2']],
                ['longText' => 'Chandkheda', 'shortText' => 'Chandkheda', 'types' => ['sublocality_level_1', 'sublocality']],
                ['longText' => 'Ahmedabad', 'shortText' => 'Ahmedabad', 'types' => ['locality']],
                ['longText' => 'Gujarat', 'shortText' => 'GJ', 'types' => ['administrative_area_level_1']],
                ['longText' => '382424', 'shortText' => '382424', 'types' => ['postal_code']],
            ],
            'types' => ['premise', 'establishment', 'point_of_interest'],
        ], 200),
    ]);

    // 1. Test searchProjects directly
    $projects = GooglePlacesService::searchProjects('Kalash Square');
    expect($projects)->not->toBeEmpty();
    $firstProject = $projects[0];
    expect($firstProject['name'])->toBe('Kalash Square')
        ->and($firstProject['locality'])->toBe('Chandkheda')
        ->and($firstProject['city'])->toBe('Ahmedabad')
        ->and($firstProject['pincode'])->toBe('382424');

    // 2. Assert stored in DB cache
    $this->assertDatabaseHas('google_places', [
        'place_id' => 'ChIJ_KalashSquareChandkheda',
        'name' => 'Kalash Square',
        'locality' => 'Chandkheda',
        'city' => 'Ahmedabad',
        'pincode' => '382424',
    ]);

    // 3. Test suggestions endpoint
    $res = $this->getJson('/api/search/suggestions?q=Kalash Square');
    $res->assertStatus(200)
        ->assertJsonFragment([
            'type' => 'project',
            'category' => 'Apartment / Project',
            'title' => 'Kalash Square',
            'city' => 'Ahmedabad',
        ]);
});
