<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Locality;
use App\Services\GeocodingService;
use App\Services\GooglePlacesService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class LocationController extends Controller
{
    /**
     * Get list of featured metro cities or cities in a state.
     */
    public function cities(Request $request): JsonResponse
    {
        $stateId = $request->query('state_id');
        $metrosOnly = $request->boolean('metros', false);

        $cacheKey = "loc_cities_s{$stateId}_m".($metrosOnly ? '1' : '0');
        $cities = Cache::remember($cacheKey, now()->addHours(24), function () use ($stateId, $metrosOnly) {
            $query = City::with('state:id,name');

            if ($stateId) {
                $query->where('state_id', $stateId);
            }

            if ($metrosOnly) {
                $query->where('is_metro', true);
            }

            return $query->orderBy('is_metro', 'desc')->orderBy('name')->take(50)->get();
        });

        return response()->json($cities)->header('Cache-Control', 'public, max-age=86400');
    }

    /**
     * Get localities for a specific city.
     */
    public function localities(Request $request): JsonResponse
    {
        $cityId = $request->query('city_id');
        $cityName = $request->query('city');

        $cacheKey = 'loc_localities_'.md5("cid:{$cityId}_cn:{$cityName}");
        $localities = Cache::remember($cacheKey, now()->addHours(24), function () use ($cityId, $cityName) {
            $query = Locality::query();

            if ($cityId) {
                $query->where('city_id', $cityId);
            } elseif ($cityName) {
                $city = City::where('name', $cityName)->first();
                if ($city) {
                    $query->where('city_id', $city->id);
                }
            }

            return $query->orderBy('name')->get();
        });

        return response()->json($localities)->header('Cache-Control', 'public, max-age=86400');
    }

    /**
     * Resolve latitude, longitude, and pincode based on locality, city, and address with Google Places caching.
     */
    public function resolve(Request $request): JsonResponse
    {
        $resolved = GooglePlacesService::resolve(
            city: $request->query('city') ?? $request->input('city'),
            locality: $request->query('locality') ?? $request->input('locality'),
            projectName: $request->query('project_name') ?? $request->input('project_name'),
            subLocality: $request->query('sub_locality') ?? $request->input('sub_locality'),
            address: $request->query('address') ?? $request->input('address'),
            landmark: $request->query('landmark') ?? $request->input('landmark'),
            pincode: $request->query('pincode') ?? $request->input('pincode'),
            latitude: $request->query('lat') ? (float) $request->query('lat') : ($request->input('latitude') ? (float) $request->input('latitude') : null),
            longitude: $request->query('lng') ? (float) $request->query('lng') : ($request->input('longitude') ? (float) $request->input('longitude') : null),
        );

        return response()->json([
            'status' => 'success',
            'data' => $resolved,
        ]);
    }

    /**
     * Autocomplete search for localities, landmarks, and cities via Google Places API & DB cache.
     */
    public function search(Request $request): JsonResponse
    {
        $q = trim($request->query('q', ''));
        $city = trim((string) $request->query('city', ''));

        if (strlen($q) < 1) {
            return response()->json([]);
        }

        // 1. Search Google Places API + DB Cached Places
        $places = GooglePlacesService::searchPlaces(
            query: $q,
            city: $city ?: null,
            limit: 8
        );

        $results = collect($places)->map(function ($place) {
            $name = $place['name'] ?: ($place['locality'] ?: 'Place');

            return [
                'id' => $place['place_id'],
                'name' => $name,
                'title' => $place['title'] ?? $name,
                'type' => 'locality',
                'city' => $place['city'],
                'locality' => $place['locality'],
                'sub_locality' => $place['sub_locality'],
                'state' => $place['state'],
                'display' => $place['display'],
                'pincode' => $place['pincode'],
                'latitude' => $place['latitude'],
                'longitude' => $place['longitude'],
                'cached' => $place['cached'],
                'provider' => $place['provider'],
            ];
        });

        // 2. Search in database cities
        $cities = City::with('state')
            ->where('name', 'LIKE', "%{$q}%")
            ->take(3)
            ->get()
            ->map(function ($c) {
                return [
                    'id' => (string) $c->id,
                    'name' => $c->name,
                    'type' => 'city',
                    'city' => $c->name,
                    'locality' => null,
                    'sub_locality' => null,
                    'state' => $c->state?->name,
                    'display' => "{$c->name}, {$c->state?->name}",
                    'pincode' => null,
                    'latitude' => null,
                    'longitude' => null,
                    'cached' => true,
                    'provider' => 'database',
                ];
            });

        $combined = $results->concat($cities)->unique(function ($item) {
            return strtolower(trim(($item['name'] ?? '').' '.($item['city'] ?? '')));
        })->values()->take(10);

        return response()->json($combined);
    }

    /**
     * Unified location discovery searching cities, localities, societies, apartments, and landmarks.
     * Used by Post Property and global search to auto-fill location fields.
     */
    public function searchAll(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));
        $city = trim((string) $request->query('city', ''));
        $limit = (int) $request->query('limit', 10);

        $results = GooglePlacesService::searchAll($q, $limit, $city ?: null);

        return response()->json($results);
    }

    /**
     * Search cities and states using Google Places API (New) & database cache.
     */
    public function searchCities(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));
        if (strlen($q) < 1) {
            // Default top metro cities
            $cities = City::with('state')
                ->where('is_metro', true)
                ->orderBy('name')
                ->take(8)
                ->get()
                ->map(fn ($c) => [
                    'place_id' => 'city_'.$c->id,
                    'name' => $c->name,
                    'city' => $c->name,
                    'state' => $c->state?->name ?? GooglePlacesService::getStateForCity($c->name),
                    'display' => "{$c->name}, ".($c->state?->name ?? GooglePlacesService::getStateForCity($c->name)),
                    'latitude' => (float) $c->latitude,
                    'longitude' => (float) $c->longitude,
                    'cached' => true,
                    'provider' => 'database',
                ]);

            return response()->json($cities);
        }

        $cities = GooglePlacesService::searchCities($q, 8);

        return response()->json($cities);
    }

    /**
     * Search apartment, society, and project names via Google Places API (New) & listings.
     */
    public function searchProjects(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));
        $city = trim((string) $request->query('city', ''));
        $locality = trim((string) $request->query('locality', ''));

        if (strlen($q) < 1) {
            return response()->json([]);
        }

        $projects = GooglePlacesService::searchProjects($q, $city ?: null, $locality ?: null, 8);

        return response()->json($projects);
    }

    /**
     * Detect user's city and locality from GPS coordinates.
     */
    public function detect(Request $request): JsonResponse
    {
        $lat = $request->float('lat') ?: null;
        $lng = $request->float('lng') ?: null;

        $detectedCity = null;
        $detectedLocality = null;
        $detectedState = null;

        if ($lat && $lng) {
            try {
                $response = Http::timeout(4)
                    ->withHeaders(['User-Agent' => 'RealHemApp/1.0'])
                    ->get('https://nominatim.openstreetmap.org/reverse', [
                        'lat' => $lat,
                        'lon' => $lng,
                        'format' => 'json',
                    ]);

                if ($response->successful()) {
                    $data = $response->json();
                    $addr = $data['address'] ?? [];

                    $rawCity = $addr['city'] ?? $addr['town'] ?? $addr['state_district'] ?? $addr['county'] ?? null;
                    $detectedLocality = $addr['suburb'] ?? $addr['neighbourhood'] ?? $addr['village'] ?? $addr['road'] ?? null;
                    $detectedState = $addr['state'] ?? null;

                    $detectedCity = GeocodingService::normalizeCityName($rawCity, $detectedState, $detectedLocality);
                }
            } catch (\Throwable $e) {
                // Ignore network errors and fallback gracefully
            }
        }

        $detectedCity = $detectedCity ?: 'Ahmedabad';
        $nearbyList = GeocodingService::getNearbyLocalities($detectedCity, $detectedLocality, $lat, $lng);

        return response()->json([
            'success' => true,
            'city' => $detectedCity,
            'locality' => $detectedLocality,
            'state' => $detectedState,
            'display' => $detectedLocality ? "{$detectedLocality}, {$detectedCity}" : $detectedCity,
            'lat' => $lat,
            'lng' => $lng,
            'nearby_localities' => $nearbyList,
        ]);
    }

    /**
     * Get nearby localities within real radius (default 3.0 km) for a given city and locality/coordinates.
     */
    public function nearby(Request $request): JsonResponse
    {
        $city = (string) $request->query('city', 'Ahmedabad');
        $locality = $request->query('locality');
        $lat = $request->float('lat') ?: null;
        $lng = $request->float('lng') ?: null;
        $radiusKm = (float) $request->query('radius', 3.0);

        $cacheKey = 'loc_nearby_'.md5("{$city}_{$locality}_{$lat}_{$lng}_{$radiusKm}");
        $data = Cache::remember($cacheKey, now()->addHours(2), function () use ($city, $locality, $lat, $lng, $radiusKm) {
            $nearbyList = GeocodingService::getNearbyLocalities(
                $city,
                $locality,
                $lat,
                $lng,
                $radiusKm
            );

            return [
                'success' => true,
                'city' => $city,
                'locality' => $locality,
                'lat' => $lat,
                'lng' => $lng,
                'radius_km' => $radiusKm,
                'nearby_localities' => $nearbyList,
            ];
        });

        return response()->json($data)->header('Cache-Control', 'public, max-age=1800');
    }

    /**
     * Helper delegate for retrieving nearby localities.
     *
     * @return array<int, string>
     */
    public function getNearbyLocalities(string $city, ?string $locality = null, ?float $lat = null, ?float $lng = null, float $radiusKm = 3.0): array
    {
        return GeocodingService::getNearbyLocalities($city, $locality, $lat, $lng, $radiusKm);
    }

    /**
     * Helper delegate for calculating geographical distance in kilometers.
     */
    public function calculateHaversineDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        return GeocodingService::distance($lat1, $lon1, $lat2, $lon2);
    }

    /**
     * Helper delegate for normalizing city market names.
     */
    public function normalizeCityName(?string $rawCity, ?string $state = null, ?string $locality = null): string
    {
        return GeocodingService::normalizeCityName($rawCity, $state, $locality);
    }
}
