<?php

namespace App\Services;

use App\Models\City;
use App\Models\GooglePlace;
use App\Models\Locality;
use App\Models\Property;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GooglePlacesService
{
    /**
     * Search places and localities using DB cache first, Google Places API (New) second, and local dictionary third.
     * All places retrieved from Google Places are persisted to `google_places` table to minimize API calls.
     *
     * @return array<int, array{
     *     place_id: ?string,
     *     title: string,
     *     name: string,
     *     display: string,
     *     formatted_address: string,
     *     locality: string,
     *     city: ?string,
     *     sub_locality: ?string,
     *     state: ?string,
     *     pincode: ?string,
     *     latitude: float,
     *     longitude: float,
     *     cached: bool,
     *     provider: string
     * }>
     */
    public static function searchPlaces(
        string $query,
        ?string $city = null,
        int $limit = 6,
        ?float $proximityLat = null,
        ?float $proximityLng = null
    ): array {
        $rawQuery = trim($query);
        if (strlen($rawQuery) < 1) {
            return [];
        }

        $cleanQuery = strtolower(preg_replace('/\s+/', ' ', preg_replace('/[^\w\s]/', '', $rawQuery)));
        $results = [];
        $seenKeys = [];

        // 1. Fetch matching places already in DB cache table
        $cachedPlaces = GooglePlace::query()
            ->where(function ($q) use ($rawQuery, $cleanQuery) {
                $q->where('normalized_query', 'LIKE', "%{$cleanQuery}%")
                    ->orWhere('locality', 'LIKE', "%{$rawQuery}%")
                    ->orWhere('name', 'LIKE', "%{$rawQuery}%")
                    ->orWhere('formatted_address', 'LIKE', "%{$rawQuery}%")
                    ->orWhere('sub_locality', 'LIKE', "%{$rawQuery}%");
            });

        if (! empty($city)) {
            $cachedPlaces->where(function ($q) use ($city) {
                $q->where('city', 'LIKE', "%{$city}%")
                    ->orWhereNull('city');
            });
        }

        $dbHits = $cachedPlaces->orderByDesc('hit_count')->take($limit)->get();

        foreach ($dbHits as $place) {
            $displayName = $place->name ?: ($place->locality ?: 'Place');
            $key = strtolower(trim($displayName.' '.($place->locality ?: '').' '.($place->city ?: '')));
            if (! isset($seenKeys[$key])) {
                $seenKeys[$key] = true;
                $place->increment('hit_count');

                $results[] = [
                    'place_id' => $place->place_id,
                    'title' => $displayName,
                    'name' => $place->name ?: ($place->locality ?: ''),
                    'display' => $place->formatted_address,
                    'formatted_address' => $place->formatted_address,
                    'locality' => $place->locality ?: $place->name,
                    'city' => $place->city,
                    'sub_locality' => $place->sub_locality,
                    'state' => $place->state ?: self::getStateForCity($place->city),
                    'pincode' => $place->pincode,
                    'latitude' => (float) $place->latitude,
                    'longitude' => (float) $place->longitude,
                    'cached' => true,
                    'provider' => $place->provider,
                ];
            }
        }

        // 2. Query Google Places API (New) if key is configured and more results needed
        $apiKey = config('services.google.places_api_key');
        if ($apiKey && count($results) < $limit) {
            try {
                $searchParts = [$rawQuery];
                if ($city && ! str_contains(strtolower($rawQuery), strtolower($city)) && count(explode(' ', $rawQuery)) === 1) {
                    $searchParts[] = $city;
                }
                $searchParts[] = 'India';
                $fullSearch = implode(', ', array_unique($searchParts));

                $autoPayload = [
                    'input' => $fullSearch,
                    'includedRegionCodes' => ['IN'],
                    'languageCode' => 'en',
                ];

                if ($proximityLat !== null && $proximityLng !== null) {
                    $autoPayload['locationBias'] = [
                        'circle' => [
                            'center' => [
                                'latitude' => $proximityLat,
                                'longitude' => $proximityLng,
                            ],
                            'radius' => 50000.0,
                        ],
                    ];
                }

                $response = Http::timeout(5)
                    ->withHeaders([
                        'Content-Type' => 'application/json',
                        'X-Goog-Api-Key' => $apiKey,
                    ])
                    ->post('https://places.googleapis.com/v1/places:autocomplete', $autoPayload);

                if ($response->successful()) {
                    $suggestions = $response->json('suggestions') ?? [];

                    foreach ($suggestions as $sug) {
                        if (count($results) >= $limit) {
                            break;
                        }

                        $pred = $sug['placePrediction'] ?? null;
                        if (! $pred) {
                            continue;
                        }

                        $placeId = $pred['placeId'] ?? (isset($pred['place']) ? str_replace('places/', '', $pred['place']) : null);
                        if (! $placeId) {
                            continue;
                        }

                        // Check if place_id already in DB cache
                        $existing = GooglePlace::where('place_id', $placeId)->first();
                        if ($existing) {
                            $existing->increment('hit_count');
                            $displayName = $existing->name ?: ($existing->locality ?: 'Place');
                            $key = strtolower(trim($displayName.' '.($existing->locality ?: '').' '.($existing->city ?: '')));
                            if (! isset($seenKeys[$key])) {
                                $seenKeys[$key] = true;
                                $results[] = [
                                    'place_id' => $existing->place_id,
                                    'title' => $displayName,
                                    'name' => $existing->name ?: ($existing->locality ?: ''),
                                    'display' => $existing->formatted_address,
                                    'formatted_address' => $existing->formatted_address,
                                    'locality' => $existing->locality ?: $existing->name,
                                    'city' => $existing->city,
                                    'sub_locality' => $existing->sub_locality,
                                    'state' => $existing->state ?: self::getStateForCity($existing->city),
                                    'pincode' => $existing->pincode,
                                    'latitude' => (float) $existing->latitude,
                                    'longitude' => (float) $existing->longitude,
                                    'cached' => true,
                                    'provider' => 'google_places_new',
                                ];
                            }

                            continue;
                        }

                        // Fetch Place Details from Places API (New)
                        $cleanId = str_replace('places/', '', $placeId);
                        $detailsRes = Http::timeout(5)
                            ->withHeaders([
                                'Content-Type' => 'application/json',
                                'X-Goog-Api-Key' => $apiKey,
                                'X-Goog-FieldMask' => 'id,displayName,formattedAddress,location,addressComponents,types',
                            ])
                            ->get("https://places.googleapis.com/v1/places/{$cleanId}");

                        if ($detailsRes->successful()) {
                            $detail = $detailsRes->json();
                            $locObj = $detail['location'] ?? [];
                            $lat = isset($locObj['latitude']) ? (float) $locObj['latitude'] : null;
                            $lng = isset($locObj['longitude']) ? (float) $locObj['longitude'] : null;

                            if ($lat !== null && $lng !== null) {
                                $parsed = self::extractNewAddressComponents($detail['addressComponents'] ?? []);
                                $formattedAddress = $detail['formattedAddress'] ?? ($pred['text']['text'] ?? $rawQuery);
                                $placeName = $detail['displayName']['text'] ?? ($pred['structuredFormat']['mainText']['text'] ?? $rawQuery);
                                $detectedLocality = $parsed['locality'] ?: $placeName;
                                $detectedCity = $parsed['city'] ?: $city;
                                $detectedState = $parsed['state'] ?: self::getStateForCity($detectedCity);

                                $norm = strtolower(preg_replace('/\s+/', ' ', preg_replace('/[^\w\s]/', '', $formattedAddress)));
                                $hash = hash('sha256', $norm);

                                $created = GooglePlace::create([
                                    'place_id' => $cleanId,
                                    'query_hash' => $hash,
                                    'raw_query' => $rawQuery,
                                    'normalized_query' => $norm,
                                    'name' => $placeName,
                                    'formatted_address' => $formattedAddress,
                                    'locality' => $detectedLocality,
                                    'sub_locality' => $parsed['sub_locality'],
                                    'city' => $detectedCity,
                                    'state' => $detectedState,
                                    'pincode' => $parsed['pincode'],
                                    'latitude' => $lat,
                                    'longitude' => $lng,
                                    'types' => $detail['types'] ?? ($pred['types'] ?? null),
                                    'raw_response' => $detail,
                                    'hit_count' => 1,
                                    'provider' => 'google_places_new',
                                ]);

                                $displayName = $created->name ?: ($created->locality ?: 'Place');
                                $key = strtolower(trim($displayName.' '.($detectedLocality ?: '').' '.($detectedCity ?: '')));
                                if (! isset($seenKeys[$key])) {
                                    $seenKeys[$key] = true;
                                    $results[] = [
                                        'place_id' => $created->place_id,
                                        'title' => $displayName,
                                        'name' => $created->name ?: ($created->locality ?: ''),
                                        'display' => $created->formatted_address,
                                        'formatted_address' => $created->formatted_address,
                                        'locality' => $created->locality,
                                        'city' => $created->city,
                                        'sub_locality' => $created->sub_locality,
                                        'state' => $created->state,
                                        'pincode' => $created->pincode,
                                        'latitude' => (float) $created->latitude,
                                        'longitude' => (float) $created->longitude,
                                        'cached' => false,
                                        'provider' => 'google_places_new',
                                    ];
                                }
                            }
                        }
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('Google Places API (New) request failed: '.$e->getMessage());
            }
        }

        // 3. Fallback: Search local database localities or dictionary fallback and prime into GooglePlace cache
        if (empty($results)) {
            $localLocs = Locality::with('city.state')
                ->where('name', 'LIKE', "%{$rawQuery}%")
                ->orWhere('pincode', 'LIKE', "%{$rawQuery}%");

            if (! empty($city)) {
                $localLocs->whereHas('city', fn ($c) => $c->where('name', 'LIKE', "%{$city}%"));
            }

            $locItems = $localLocs->take($limit)->get();

            if ($locItems->isNotEmpty()) {
                foreach ($locItems as $loc) {
                    $cityName = $loc->city?->name ?? $city ?? 'Ahmedabad';
                    $formattedAddress = "{$loc->name}, {$cityName}, India";
                    $norm = strtolower(preg_replace('/\s+/', ' ', preg_replace('/[^\w\s]/', '', $formattedAddress)));
                    $hash = hash('sha256', $norm);

                    $coords = GeocodingService::resolve(
                        city: $cityName,
                        locality: $loc->name,
                        projectName: $loc->name,
                        address: $loc->name
                    );

                    $stateName = $loc->city?->state?->name ?? self::getStateForCity($cityName);

                    $saved = GooglePlace::firstOrCreate(
                        ['query_hash' => $hash],
                        [
                            'place_id' => 'local_'.md5($formattedAddress),
                            'raw_query' => $loc->name,
                            'normalized_query' => $norm,
                            'name' => $loc->name,
                            'formatted_address' => $formattedAddress,
                            'locality' => $loc->name,
                            'sub_locality' => null,
                            'city' => $cityName,
                            'state' => $stateName,
                            'pincode' => $loc->pincode ?: $coords['pincode'],
                            'latitude' => $coords['latitude'],
                            'longitude' => $coords['longitude'],
                            'types' => ['locality'],
                            'raw_response' => null,
                            'hit_count' => 1,
                            'provider' => 'local_fallback',
                        ]
                    );

                    $key = strtolower(trim($loc->name.' '.$cityName));
                    if (! isset($seenKeys[$key])) {
                        $seenKeys[$key] = true;
                        $results[] = [
                            'place_id' => $saved->place_id,
                            'title' => $loc->name,
                            'name' => $loc->name,
                            'display' => $formattedAddress,
                            'formatted_address' => $formattedAddress,
                            'locality' => $loc->name,
                            'city' => $cityName,
                            'sub_locality' => null,
                            'state' => $stateName,
                            'pincode' => $saved->pincode,
                            'latitude' => (float) $saved->latitude,
                            'longitude' => (float) $saved->longitude,
                            'cached' => false,
                            'provider' => 'local_fallback',
                        ];
                    }
                }
            } else {
                $cityName = $city ?? 'Ahmedabad';
                $stateName = self::getStateForCity($cityName);
                $formattedAddress = "{$rawQuery}, {$cityName}, India";
                $norm = strtolower(preg_replace('/\s+/', ' ', preg_replace('/[^\w\s]/', '', $formattedAddress)));
                $hash = hash('sha256', $norm);

                $coords = GeocodingService::resolve(
                    city: $cityName,
                    locality: $rawQuery,
                    projectName: $rawQuery,
                    address: $rawQuery
                );

                $saved = GooglePlace::firstOrCreate(
                    ['query_hash' => $hash],
                    [
                        'place_id' => 'local_'.md5($formattedAddress),
                        'raw_query' => $rawQuery,
                        'normalized_query' => $norm,
                        'name' => $rawQuery,
                        'formatted_address' => $formattedAddress,
                        'locality' => $rawQuery,
                        'sub_locality' => null,
                        'city' => $cityName,
                        'state' => $stateName,
                        'pincode' => $coords['pincode'],
                        'latitude' => $coords['latitude'],
                        'longitude' => $coords['longitude'],
                        'types' => ['locality'],
                        'raw_response' => null,
                        'hit_count' => 1,
                        'provider' => 'local_fallback',
                    ]
                );

                $results[] = [
                    'place_id' => $saved->place_id,
                    'title' => $saved->name,
                    'name' => $saved->name,
                    'display' => $formattedAddress,
                    'formatted_address' => $formattedAddress,
                    'locality' => $saved->locality,
                    'city' => $cityName,
                    'sub_locality' => null,
                    'state' => $stateName,
                    'pincode' => $saved->pincode,
                    'latitude' => (float) $saved->latitude,
                    'longitude' => (float) $saved->longitude,
                    'cached' => false,
                    'provider' => 'local_fallback',
                ];
            }
        }

        return array_slice($results, 0, $limit);
    }

    /**
     * Search cities using database, cached Google places, and Google Places API (New).
     *
     * @return array<int, array{
     *     place_id: string,
     *     name: string,
     *     city: string,
     *     state: ?string,
     *     display: string,
     *     latitude: ?float,
     *     longitude: ?float,
     *     cached: bool,
     *     provider: string
     * }>
     */
    public static function searchCities(string $query, int $limit = 8): array
    {
        $rawQuery = trim($query);
        if (strlen($rawQuery) < 1) {
            return [];
        }

        $results = [];
        $seenKeys = [];

        // 1. Check database cities
        $dbCities = City::with('state')
            ->where('name', 'LIKE', "{$rawQuery}%")
            ->orWhere('name', 'LIKE', "%{$rawQuery}%")
            ->orderBy('is_metro', 'desc')
            ->orderBy('name')
            ->take($limit)
            ->get();

        foreach ($dbCities as $c) {
            $key = strtolower($c->name);
            if (! isset($seenKeys[$key])) {
                $seenKeys[$key] = true;
                $stateName = $c->state?->name ?? self::getStateForCity($c->name);
                $results[] = [
                    'place_id' => 'city_'.$c->id,
                    'name' => $c->name,
                    'city' => $c->name,
                    'state' => $stateName,
                    'display' => "{$c->name}, {$stateName}",
                    'latitude' => $c->latitude ? (float) $c->latitude : null,
                    'longitude' => $c->longitude ? (float) $c->longitude : null,
                    'cached' => true,
                    'provider' => 'database',
                ];
            }
        }

        // 2. Check cached GooglePlace records for cities
        $cachedPlaces = GooglePlace::whereNotNull('city')
            ->where(function ($q) use ($rawQuery) {
                $q->where('city', 'LIKE', "%{$rawQuery}%")
                    ->orWhere('name', 'LIKE', "%{$rawQuery}%");
            })
            ->take($limit)
            ->get();

        foreach ($cachedPlaces as $place) {
            $cityName = $place->city ?: $place->name;
            $key = strtolower($cityName);
            if (! isset($seenKeys[$key])) {
                $seenKeys[$key] = true;
                $stateName = $place->state ?: self::getStateForCity($cityName);
                $results[] = [
                    'place_id' => $place->place_id,
                    'name' => $cityName,
                    'city' => $cityName,
                    'state' => $stateName,
                    'display' => "{$cityName}, {$stateName}",
                    'latitude' => (float) $place->latitude,
                    'longitude' => (float) $place->longitude,
                    'cached' => true,
                    'provider' => 'google_places_new',
                ];
            }
        }

        // 3. Query Google Places API (New) for city predictions if token exists
        $apiKey = config('services.google.places_api_key');
        if ($apiKey && count($results) < $limit) {
            try {
                $response = Http::timeout(5)
                    ->withHeaders([
                        'Content-Type' => 'application/json',
                        'X-Goog-Api-Key' => $apiKey,
                    ])
                    ->post('https://places.googleapis.com/v1/places:autocomplete', [
                        'input' => $rawQuery.', India',
                        'includedRegionCodes' => ['IN'],
                        'languageCode' => 'en',
                    ]);

                if ($response->successful()) {
                    $suggestions = $response->json('suggestions') ?? [];
                    foreach ($suggestions as $sug) {
                        if (count($results) >= $limit) {
                            break;
                        }

                        $pred = $sug['placePrediction'] ?? null;
                        if (! $pred) {
                            continue;
                        }

                        $placeId = $pred['placeId'] ?? (isset($pred['place']) ? str_replace('places/', '', $pred['place']) : null);
                        $cityName = $pred['structuredFormat']['mainText']['text'] ?? ($pred['text']['text'] ?? $rawQuery);
                        $cleanId = str_replace('places/', '', $placeId);

                        // Fetch details
                        $detailsRes = Http::timeout(5)
                            ->withHeaders([
                                'Content-Type' => 'application/json',
                                'X-Goog-Api-Key' => $apiKey,
                                'X-Goog-FieldMask' => 'id,displayName,formattedAddress,location,addressComponents,types',
                            ])
                            ->get("https://places.googleapis.com/v1/places/{$cleanId}");

                        if ($detailsRes->successful()) {
                            $detail = $detailsRes->json();
                            $parsed = self::extractNewAddressComponents($detail['addressComponents'] ?? []);
                            $resolvedCity = $parsed['city'] ?: ($detail['displayName']['text'] ?? $cityName);
                            $resolvedState = $parsed['state'] ?: self::getStateForCity($resolvedCity);
                            $loc = $detail['location'] ?? [];

                            $key = strtolower($resolvedCity);
                            if (! isset($seenKeys[$key])) {
                                $seenKeys[$key] = true;

                                GooglePlace::firstOrCreate(
                                    ['place_id' => $cleanId],
                                    [
                                        'query_hash' => hash('sha256', strtolower($resolvedCity)),
                                        'raw_query' => $rawQuery,
                                        'normalized_query' => strtolower($resolvedCity),
                                        'name' => $resolvedCity,
                                        'formatted_address' => $detail['formattedAddress'] ?? "{$resolvedCity}, {$resolvedState}, India",
                                        'locality' => $parsed['locality'],
                                        'sub_locality' => $parsed['sub_locality'],
                                        'city' => $resolvedCity,
                                        'state' => $resolvedState,
                                        'pincode' => $parsed['pincode'],
                                        'latitude' => $loc['latitude'] ?? 0,
                                        'longitude' => $loc['longitude'] ?? 0,
                                        'types' => $detail['types'] ?? null,
                                        'raw_response' => $detail,
                                        'hit_count' => 1,
                                        'provider' => 'google_places_new',
                                    ]
                                );

                                $results[] = [
                                    'place_id' => $cleanId,
                                    'name' => $resolvedCity,
                                    'city' => $resolvedCity,
                                    'state' => $resolvedState,
                                    'display' => "{$resolvedCity}, {$resolvedState}",
                                    'latitude' => $loc['latitude'] ?? null,
                                    'longitude' => $loc['longitude'] ?? null,
                                    'cached' => false,
                                    'provider' => 'google_places_new',
                                ];
                            }
                        }
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('Google Places API (New) searchCities error: '.$e->getMessage());
            }
        }

        return array_slice($results, 0, $limit);
    }

    /**
     * Search apartments, housing complexes, and projects using database and Google Places API (New).
     *
     * @return array<int, array{
     *     place_id: string,
     *     name: string,
     *     title: string,
     *     locality: ?string,
     *     sub_locality: ?string,
     *     city: ?string,
     *     state: ?string,
     *     pincode: ?string,
     *     display: string,
     *     latitude: ?float,
     *     longitude: ?float,
     *     cached: bool,
     *     provider: string
     * }>
     */
    public static function searchProjects(string $query, ?string $city = null, ?string $locality = null, int $limit = 6): array
    {
        $rawQuery = trim($query);
        if (strlen($rawQuery) < 1) {
            return [];
        }

        $results = [];
        $seenKeys = [];

        // 1. Search in Property database listings
        $propQuery = Property::whereNotNull('project_name')
            ->where('project_name', '!=', '')
            ->where('project_name', 'LIKE', "%{$rawQuery}%");
        if ($city) {
            $propQuery->where('city', $city);
        }
        foreach ($propQuery->distinct()->take($limit)->get(['project_name', 'locality', 'sub_locality', 'city', 'state', 'pincode', 'latitude', 'longitude']) as $p) {
            $key = strtolower($p->project_name.' '.($p->city ?? ''));
            if (! isset($seenKeys[$key])) {
                $seenKeys[$key] = true;
                $results[] = [
                    'place_id' => 'proj_'.md5($p->project_name),
                    'name' => $p->project_name,
                    'title' => $p->project_name,
                    'locality' => $p->locality,
                    'sub_locality' => $p->sub_locality,
                    'city' => $p->city,
                    'state' => $p->state ?? self::getStateForCity($p->city),
                    'pincode' => $p->pincode,
                    'display' => implode(', ', array_filter([$p->project_name, $p->locality, $p->city])),
                    'latitude' => (float) $p->latitude,
                    'longitude' => (float) $p->longitude,
                    'cached' => true,
                    'provider' => 'database',
                ];
            }
        }

        // 2. Search cached places in GooglePlace table
        $cleanQuery = strtolower(preg_replace('/\s+/', ' ', preg_replace('/[^\w\s]/', '', $rawQuery)));
        $cachedQuery = GooglePlace::query()
            ->where(function ($q) use ($rawQuery, $cleanQuery) {
                $q->where('name', 'LIKE', "%{$rawQuery}%")
                    ->orWhere('formatted_address', 'LIKE', "%{$rawQuery}%")
                    ->orWhere('normalized_query', 'LIKE', "%{$cleanQuery}%");
            });

        if (! empty($city)) {
            $cachedQuery->where(function ($q) use ($city) {
                $q->where('city', 'LIKE', "%{$city}%")
                    ->orWhereNull('city');
            });
        }

        foreach ($cachedQuery->orderByDesc('hit_count')->take($limit)->get() as $cp) {
            $projectName = $cp->name ?: ($cp->locality ?: 'Project');
            $key = strtolower(trim($projectName.' '.($cp->city ?? '')));
            if (! isset($seenKeys[$key])) {
                $seenKeys[$key] = true;
                $cp->increment('hit_count');
                $results[] = [
                    'place_id' => $cp->place_id,
                    'name' => $projectName,
                    'title' => $projectName,
                    'locality' => $cp->locality,
                    'sub_locality' => $cp->sub_locality,
                    'city' => $cp->city,
                    'state' => $cp->state ?: self::getStateForCity($cp->city),
                    'pincode' => $cp->pincode,
                    'display' => $cp->formatted_address,
                    'latitude' => (float) $cp->latitude,
                    'longitude' => (float) $cp->longitude,
                    'cached' => true,
                    'provider' => $cp->provider,
                ];
            }
        }

        // 3. Query Google Places API (New) for establishments/housing complexes
        $apiKey = config('services.google.places_api_key');
        if ($apiKey && count($results) < $limit) {
            try {
                $searchParts = [$rawQuery];
                if ($locality && ! str_contains(strtolower($rawQuery), strtolower($locality))) {
                    $searchParts[] = $locality;
                }
                if ($city && ! str_contains(strtolower($rawQuery), strtolower($city)) && count(explode(' ', $rawQuery)) === 1) {
                    $searchParts[] = $city;
                }
                $searchParts[] = 'India';
                $input = implode(', ', array_unique($searchParts));

                $response = Http::timeout(5)
                    ->withHeaders([
                        'Content-Type' => 'application/json',
                        'X-Goog-Api-Key' => $apiKey,
                    ])
                    ->post('https://places.googleapis.com/v1/places:autocomplete', [
                        'input' => $input,
                        'includedRegionCodes' => ['IN'],
                        'languageCode' => 'en',
                    ]);

                if ($response->successful()) {
                    $suggestions = $response->json('suggestions') ?? [];
                    foreach ($suggestions as $sug) {
                        if (count($results) >= $limit) {
                            break;
                        }

                        $pred = $sug['placePrediction'] ?? null;
                        if (! $pred) {
                            continue;
                        }

                        $cleanId = str_replace('places/', '', $pred['placeId'] ?? '');
                        if (! $cleanId) {
                            continue;
                        }

                        // Check if place already in GooglePlace DB cache
                        $existing = GooglePlace::where('place_id', $cleanId)->first();
                        if ($existing) {
                            $existing->increment('hit_count');
                            $projectName = $existing->name ?: ($existing->locality ?: 'Project');
                            $key = strtolower(trim($projectName.' '.($existing->city ?? '')));
                            if (! isset($seenKeys[$key])) {
                                $seenKeys[$key] = true;
                                $results[] = [
                                    'place_id' => $existing->place_id,
                                    'name' => $projectName,
                                    'title' => $projectName,
                                    'locality' => $existing->locality,
                                    'sub_locality' => $existing->sub_locality,
                                    'city' => $existing->city,
                                    'state' => $existing->state ?: self::getStateForCity($existing->city),
                                    'pincode' => $existing->pincode,
                                    'display' => $existing->formatted_address,
                                    'latitude' => (float) $existing->latitude,
                                    'longitude' => (float) $existing->longitude,
                                    'cached' => true,
                                    'provider' => 'google_places_new',
                                ];
                            }

                            continue;
                        }

                        // Check details from Google Places API
                        $detailsRes = Http::timeout(5)
                            ->withHeaders([
                                'Content-Type' => 'application/json',
                                'X-Goog-Api-Key' => $apiKey,
                                'X-Goog-FieldMask' => 'id,displayName,formattedAddress,location,addressComponents,types',
                            ])
                            ->get("https://places.googleapis.com/v1/places/{$cleanId}");

                        if ($detailsRes->successful()) {
                            $detail = $detailsRes->json();
                            $parsed = self::extractNewAddressComponents($detail['addressComponents'] ?? []);
                            $projName = $detail['displayName']['text'] ?? ($pred['structuredFormat']['mainText']['text'] ?? $rawQuery);
                            $resolvedCity = $parsed['city'] ?: $city;
                            $resolvedLocality = $parsed['locality'] ?: $locality;
                            $loc = $detail['location'] ?? [];

                            $key = strtolower(trim($projName.' '.($resolvedCity ?? '')));
                            if (! isset($seenKeys[$key])) {
                                $seenKeys[$key] = true;

                                $savedPlace = GooglePlace::firstOrCreate(
                                    ['place_id' => $cleanId],
                                    [
                                        'query_hash' => hash('sha256', strtolower($projName)),
                                        'raw_query' => $rawQuery,
                                        'normalized_query' => strtolower($projName),
                                        'name' => $projName,
                                        'formatted_address' => $detail['formattedAddress'] ?? "{$projName}, {$resolvedLocality}, {$resolvedCity}",
                                        'locality' => $resolvedLocality,
                                        'sub_locality' => $parsed['sub_locality'],
                                        'city' => $resolvedCity,
                                        'state' => $parsed['state'] ?: self::getStateForCity($resolvedCity),
                                        'pincode' => $parsed['pincode'],
                                        'latitude' => $loc['latitude'] ?? 0,
                                        'longitude' => $loc['longitude'] ?? 0,
                                        'types' => $detail['types'] ?? null,
                                        'raw_response' => $detail,
                                        'hit_count' => 1,
                                        'provider' => 'google_places_new',
                                    ]
                                );

                                $results[] = [
                                    'place_id' => $cleanId,
                                    'name' => $projName,
                                    'title' => $projName,
                                    'locality' => $resolvedLocality,
                                    'sub_locality' => $parsed['sub_locality'],
                                    'city' => $resolvedCity,
                                    'state' => $savedPlace->state ?: self::getStateForCity($resolvedCity),
                                    'pincode' => $parsed['pincode'],
                                    'display' => $detail['formattedAddress'] ?? implode(', ', array_filter([$projName, $resolvedLocality, $resolvedCity])),
                                    'latitude' => $loc['latitude'] ?? null,
                                    'longitude' => $loc['longitude'] ?? null,
                                    'cached' => false,
                                    'provider' => 'google_places_new',
                                ];
                            }
                        }
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('Google Places API (New) searchProjects error: '.$e->getMessage());
            }
        }

        return array_slice($results, 0, $limit);
    }

    /**
     * Resolve latitude, longitude, and pincode using DB cache first, Google Places second, and local dictionary third.
     *
     * @return array{latitude: float, longitude: float, pincode: string, state: ?string, cached: bool, provider: string}
     */
    public static function resolve(
        ?string $city,
        ?string $locality,
        ?string $projectName = null,
        ?string $subLocality = null,
        ?string $address = null,
        ?string $landmark = null,
        ?string $pincode = null,
        ?float $latitude = null,
        ?float $longitude = null
    ): array {
        $state = self::getStateForCity($city);

        // If explicit coordinates and pincode are provided, return immediately
        if ($pincode && $latitude !== null && $latitude != 0 && $longitude !== null && $longitude != 0) {
            return [
                'latitude' => (float) $latitude,
                'longitude' => (float) $longitude,
                'pincode' => $pincode,
                'state' => $state,
                'cached' => false,
                'provider' => 'user_input',
            ];
        }

        $query = implode(' ', array_filter([$projectName, $locality, $subLocality, $landmark]));
        if (empty($query)) {
            $query = $city ?? 'Ahmedabad';
        }

        $clean = strtolower(preg_replace('/\s+/', ' ', preg_replace('/[^\w\s]/', '', $query)));
        $queryHash = hash('sha256', $clean);

        // 1. Check if place already in GooglePlace DB cache
        $cached = GooglePlace::where('query_hash', $queryHash)
            ->orWhere('normalized_query', $clean)
            ->first();

        if ($cached) {
            $cached->increment('hit_count');

            return [
                'latitude' => (float) $cached->latitude,
                'longitude' => (float) $cached->longitude,
                'pincode' => $cached->pincode ?: '380001',
                'state' => $cached->state ?: $state,
                'cached' => true,
                'provider' => $cached->provider,
            ];
        }

        // 2. Fallback to built-in GeocodingService & save in GooglePlace DB table
        $fallback = GeocodingService::resolve(
            city: $city,
            locality: $locality ?: $query,
            projectName: $projectName,
            subLocality: $subLocality,
            address: $address,
            landmark: $landmark,
            pincode: $pincode,
            latitude: $latitude,
            longitude: $longitude
        );

        $saved = GooglePlace::create([
            'place_id' => 'geo_'.md5($query.' '.($city ?? '')),
            'query_hash' => $queryHash,
            'raw_query' => $query,
            'normalized_query' => $clean,
            'name' => $locality ?: $query,
            'formatted_address' => "{$query}, ".($city ?? 'Ahmedabad'),
            'locality' => $locality ?: $query,
            'sub_locality' => $subLocality,
            'city' => $city ?? 'Ahmedabad',
            'state' => $state,
            'pincode' => $fallback['pincode'],
            'latitude' => $fallback['latitude'],
            'longitude' => $fallback['longitude'],
            'types' => ['geocode'],
            'raw_response' => null,
            'hit_count' => 1,
            'provider' => 'local_fallback',
        ]);

        return [
            'latitude' => (float) $saved->latitude,
            'longitude' => (float) $saved->longitude,
            'pincode' => $saved->pincode,
            'state' => $saved->state ?: $state,
            'cached' => false,
            'provider' => 'local_fallback',
        ];
    }

    /**
     * Resolve the Indian state for a given city name.
     */
    public static function getStateForCity(?string $city): ?string
    {
        if (! $city) {
            return null;
        }

        $clean = trim($city);

        // 1. Check City database model
        $dbCity = City::with('state')->where('name', $clean)->orWhere('name', 'LIKE', "%{$clean}%")->first();
        if ($dbCity?->state?->name) {
            return $dbCity->state->name;
        }

        // 2. Check cached GooglePlace
        $cachedPlace = GooglePlace::whereNotNull('state')
            ->where(function ($q) use ($clean) {
                $q->where('city', $clean)
                    ->orWhere('name', $clean);
            })->first();

        if ($cachedPlace?->state) {
            return $cachedPlace->state;
        }

        // 3. Indian State mapping dictionary
        $map = [
            'ahmedabad' => 'Gujarat',
            'gandhinagar' => 'Gujarat',
            'surat' => 'Gujarat',
            'vadodara' => 'Gujarat',
            'rajkot' => 'Gujarat',
            'bhavnagar' => 'Gujarat',
            'jamnagar' => 'Gujarat',
            'junagadh' => 'Gujarat',
            'delhi' => 'Delhi',
            'new delhi' => 'Delhi',
            'delhi ncr' => 'Delhi',
            'noida' => 'Uttar Pradesh',
            'greater noida' => 'Uttar Pradesh',
            'ghaziabad' => 'Uttar Pradesh',
            'lucknow' => 'Uttar Pradesh',
            'kanpur' => 'Uttar Pradesh',
            'agra' => 'Uttar Pradesh',
            'varanasi' => 'Uttar Pradesh',
            'prayagraj' => 'Uttar Pradesh',
            'meerut' => 'Uttar Pradesh',
            'gurugram' => 'Haryana',
            'gurgaon' => 'Haryana',
            'faridabad' => 'Haryana',
            'panipat' => 'Haryana',
            'sonipat' => 'Haryana',
            'karnal' => 'Haryana',
            'rohtak' => 'Haryana',
            'mumbai' => 'Maharashtra',
            'pune' => 'Maharashtra',
            'thane' => 'Maharashtra',
            'navi mumbai' => 'Maharashtra',
            'nagpur' => 'Maharashtra',
            'nashik' => 'Maharashtra',
            'aurangabad' => 'Maharashtra',
            'solapur' => 'Maharashtra',
            'kolhapur' => 'Maharashtra',
            'bangalore' => 'Karnataka',
            'bengaluru' => 'Karnataka',
            'mysore' => 'Karnataka',
            'mangalore' => 'Karnataka',
            'hubli' => 'Karnataka',
            'belgaum' => 'Karnataka',
            'hyderabad' => 'Telangana',
            'secunderabad' => 'Telangana',
            'warangal' => 'Telangana',
            'chennai' => 'Tamil Nadu',
            'coimbatore' => 'Tamil Nadu',
            'madurai' => 'Tamil Nadu',
            'tiruchirappalli' => 'Tamil Nadu',
            'salem' => 'Tamil Nadu',
            'kolkata' => 'West Bengal',
            'howrah' => 'West Bengal',
            'durgapur' => 'West Bengal',
            'asansol' => 'West Bengal',
            'siliguri' => 'West Bengal',
            'jaipur' => 'Rajasthan',
            'jodhpur' => 'Rajasthan',
            'udaipur' => 'Rajasthan',
            'kota' => 'Rajasthan',
            'bikaner' => 'Rajasthan',
            'chandigarh' => 'Chandigarh',
            'indore' => 'Madhya Pradesh',
            'bhopal' => 'Madhya Pradesh',
            'gwalior' => 'Madhya Pradesh',
            'jabalpur' => 'Madhya Pradesh',
            'kochi' => 'Kerala',
            'thiruvananthapuram' => 'Kerala',
            'kozhikode' => 'Kerala',
            'patna' => 'Bihar',
            'bhubaneswar' => 'Odisha',
            'cuttack' => 'Odisha',
            'dehradun' => 'Uttarakhand',
            'ranchi' => 'Jharkhand',
            'raipur' => 'Chhattisgarh',
            'guwahati' => 'Assam',
            'goa' => 'Goa',
            'panaji' => 'Goa',
            'amritsar' => 'Punjab',
            'ludhiana' => 'Punjab',
            'shimla' => 'Himachal Pradesh',
            'srinagar' => 'Jammu and Kashmir',
        ];

        $lower = strtolower($clean);
        foreach ($map as $key => $state) {
            if (str_contains($lower, $key) || str_contains($key, $lower)) {
                return $state;
            }
        }

        return 'Gujarat';
    }

    /**
     * Extract structured locality, sub_locality, city, state, and pincode from Google Places API (New) address components.
     *
     * @param  array<int, array{longText?: string, shortText?: string, long_name?: string, short_name?: string, types: array<string>}>  $components
     * @return array{locality: ?string, sub_locality: ?string, city: ?string, state: ?string, pincode: ?string}
     */
    protected static function extractNewAddressComponents(array $components): array
    {
        $subLocality1 = null;
        $subLocality2 = null;
        $localityType = null;
        $admin2 = null;
        $admin1 = null;
        $pincode = null;

        foreach ($components as $comp) {
            $types = $comp['types'] ?? [];
            $name = $comp['longText'] ?? ($comp['long_name'] ?? '');

            if (in_array('postal_code', $types)) {
                $pincode = $name;
            } elseif (in_array('sublocality_level_2', $types)) {
                $subLocality2 = $name;
            } elseif (in_array('sublocality_level_1', $types) || in_array('sublocality', $types)) {
                $subLocality1 = $name;
            } elseif (in_array('locality', $types)) {
                $localityType = $name;
            } elseif (in_array('administrative_area_level_2', $types)) {
                $admin2 = $name;
            } elseif (in_array('administrative_area_level_1', $types)) {
                $admin1 = $name;
            }
        }

        // In India, Google Places usually marks:
        // - sublocality_level_1 as the neighborhood/area (e.g. Chandkheda, Tragad, Vastrapur)
        // - locality as the City (e.g. Ahmedabad, Bengaluru)
        // - administrative_area_level_2 as District
        // - administrative_area_level_1 as State (e.g. Gujarat)
        if ($subLocality1 && $localityType) {
            $locality = $subLocality1;
            $subLocality = $subLocality2;
            $city = $localityType;
        } elseif ($subLocality1) {
            $locality = $subLocality1;
            $subLocality = $subLocality2;
            $city = $admin2;
        } else {
            $locality = $localityType ?: $admin2;
            $subLocality = $subLocality2;
            $city = $admin2 ?: $localityType;
        }

        return [
            'locality' => $locality,
            'sub_locality' => $subLocality,
            'city' => $city,
            'state' => $admin1,
            'pincode' => $pincode,
        ];
    }
}
