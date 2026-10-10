<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Locality;
use App\Models\State;
use App\Services\GooglePlacesService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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

        $query = City::with('state:id,name');

        if ($stateId) {
            $query->where('state_id', $stateId);
        }

        if ($metrosOnly) {
            $query->where('is_metro', true);
        }

        $cities = $query->orderBy('is_metro', 'desc')->orderBy('name')->take(50)->get();

        return response()->json($cities);
    }

    /**
     * Get localities for a specific city.
     */
    public function localities(Request $request): JsonResponse
    {
        $cityId = $request->query('city_id');
        $cityName = $request->query('city');

        $query = Locality::query();

        if ($cityId) {
            $query->where('city_id', $cityId);
        } elseif ($cityName) {
            $city = City::where('name', $cityName)->first();
            if ($city) {
                $query->where('city_id', $city->id);
            }
        }

        $localities = $query->orderBy('name')->get();

        return response()->json($localities);
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
        $lat = $request->input('lat') ?? $request->query('lat');
        $lng = $request->input('lng') ?? $request->query('lng');

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

                    $detectedCity = $this->normalizeCityName($rawCity, $detectedState, $detectedLocality);
                }
            } catch (\Throwable $e) {
                // Ignore network errors and fallback gracefully
            }
        }

        if (! $detectedCity) {
            $detectedCity = 'Delhi NCR';
        }

        $nearbyList = $this->getNearbyLocalities($detectedCity, $detectedLocality, $lat ? (float) $lat : null, $lng ? (float) $lng : null);

        return response()->json([
            'success' => true,
            'city' => $detectedCity,
            'locality' => $detectedLocality,
            'state' => $detectedState,
            'display' => $detectedLocality ? "{$detectedLocality}, {$detectedCity}" : $detectedCity,
            'lat' => $lat ? (float) $lat : null,
            'lng' => $lng ? (float) $lng : null,
            'nearby_localities' => $nearbyList,
        ]);
    }

    /**
     * Get nearby localities within 3-5 km for a given city and locality.
     */
    public function nearby(Request $request): JsonResponse
    {
        $city = $request->query('city', 'Ahmedabad');
        $locality = $request->query('locality');
        $lat = $request->query('lat');
        $lng = $request->query('lng');

        $nearbyList = $this->getNearbyLocalities(
            $city,
            $locality,
            $lat ? (float) $lat : null,
            $lng ? (float) $lng : null
        );

        return response()->json([
            'success' => true,
            'city' => $city,
            'locality' => $locality,
            'radius_km' => 3,
            'nearby_localities' => $nearbyList,
        ]);
    }

    /**
     * Resolve nearby localities list based on city and locality.
     */
    public function getNearbyLocalities(string $city, ?string $locality = null, ?float $lat = null, ?float $lng = null): array
    {
        // Try fetching localities from database
        $dbLocalities = Locality::whereHas('city', function ($q) use ($city) {
            $q->where('name', $city)
                ->orWhere('name', 'LIKE', "%{$city}%");
        })->pluck('name')->toArray();

        if (count($dbLocalities) > 0) {
            return $dbLocalities;
        }

        $clusters = [
            'Ahmedabad' => [
                'Jagatpur', 'Chandkheda', 'Zundal', 'Tragad', 'Vaishnodevi Circle',
                'Gota', 'Charodi', 'New Ranip', 'New CG Road', 'Nigam Nagar',
                'Chainpur', 'D Cabin', 'Janta Nagar', 'SG Highway', 'Ranip',
                'Sabarmati', 'Chamunda Nagar', 'Godrej Garden City', 'Motera',
                'Chandlodiya', 'Anand Nagar', 'Koteshwar', 'Khodiyar',
            ],
            'Delhi NCR' => [
                'Sector 100', 'Sector 104', 'Sector 137', 'Sector 128 (Expressway)',
                'Sector 93', 'Sector 75', 'Sector 78', 'Sector 76', 'Sector 150',
                'Sector 62', 'Noida Extension', 'Golf Course Road',
            ],
            'Bangalore' => [
                'Whitefield', 'Hoodi', 'Kadugodi', 'ITPL', 'Varthur',
                'Marathahalli', 'Brookefield', 'KR Puram', 'Mahadevapura',
            ],
            'Mumbai' => [
                'Bandra West', 'Bandra East', 'Khar West', 'Santacruz West',
                'Juhu', 'Andheri West', 'BKC', 'Pali Hill',
            ],
            'Pune' => [
                'Hinjawadi Phase 1', 'Hinjawadi Phase 2', 'Wakad', 'Baner',
                'Balewadi', 'Tathawade', 'Punawale', 'Pimple Saudagar',
            ],
        ];

        return $clusters[$city] ?? ['SG Highway', 'Bopal', 'Prahlad Nagar', 'Satellite'];
    }

    /**
     * Normalize reverse-geocoded location data to our standard Indian real estate city markets.
     */
    protected function normalizeCityName(?string $rawCity, ?string $state, ?string $locality): string
    {
        $combined = strtolower("{$rawCity} {$state} {$locality}");

        if (str_contains($combined, 'ahmedabad') || str_contains($combined, 'gandhinagar')) {
            return 'Ahmedabad';
        }
        if (str_contains($combined, 'delhi') || str_contains($combined, 'noida') || str_contains($combined, 'gurugram') || str_contains($combined, 'gurgaon') || str_contains($combined, 'ghaziabad') || str_contains($combined, 'faridabad')) {
            return 'Delhi NCR';
        }
        if (str_contains($combined, 'mumbai') || str_contains($combined, 'thane') || str_contains($combined, 'navi mumbai')) {
            return 'Mumbai';
        }
        if (str_contains($combined, 'bangalore') || str_contains($combined, 'bengaluru')) {
            return 'Bangalore';
        }
        if (str_contains($combined, 'pune') || str_contains($combined, 'pcmc') || str_contains($combined, 'pimpri')) {
            return 'Pune';
        }
        if (str_contains($combined, 'hyderabad') || str_contains($combined, 'secunderabad')) {
            return 'Hyderabad';
        }
        if (str_contains($combined, 'chennai') || str_contains($combined, 'madras')) {
            return 'Chennai';
        }
        if (str_contains($combined, 'kolkata') || str_contains($combined, 'calcutta')) {
            return 'Kolkata';
        }

        return $rawCity ?: 'Delhi NCR';
    }
}
