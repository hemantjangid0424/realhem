<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Locality;
use App\Models\State;
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
     * Autocomplete search for localities, landmarks, and cities.
     */
    public function search(Request $request): JsonResponse
    {
        $q = trim($request->query('q', ''));

        if (strlen($q) < 2) {
            return response()->json([]);
        }

        // Search in localities
        $localities = Locality::with('city.state')
            ->where('name', 'LIKE', "%{$q}%")
            ->orWhere('pincode', 'LIKE', "%{$q}%")
            ->take(8)
            ->get()
            ->map(function ($loc) {
                return [
                    'id' => $loc->id,
                    'name' => $loc->name,
                    'type' => 'locality',
                    'city' => $loc->city?->name,
                    'state' => $loc->city?->state?->name,
                    'display' => "{$loc->name}, {$loc->city?->name}",
                    'pincode' => $loc->pincode,
                    'avg_price_per_sqft' => $loc->avg_price_per_sqft,
                    'yoy_growth_percent' => $loc->yoy_growth_percent,
                ];
            });

        // Search in cities
        $cities = City::with('state')
            ->where('name', 'LIKE', "%{$q}%")
            ->take(4)
            ->get()
            ->map(function ($c) {
                return [
                    'id' => $c->id,
                    'name' => $c->name,
                    'type' => 'city',
                    'city' => $c->name,
                    'state' => $c->state?->name,
                    'display' => "{$c->name}, {$c->state?->name}",
                ];
            });

        $results = $localities->concat($cities)->take(10);

        return response()->json($results);
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
