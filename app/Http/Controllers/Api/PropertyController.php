<?php

namespace App\Http\Controllers\Api;

use App\Filters\PropertyFilter;
use App\Http\Controllers\Controller;
use App\Http\Requests\Property\StorePropertyRequest;
use App\Http\Requests\Property\UpdatePropertyRequest;
use App\Http\Requests\Property\UpdatePropertyStatusRequest;
use App\Http\Requests\Property\UploadPropertyPhotosRequest;
use App\Http\Requests\Property\UploadPropertyVideosRequest;
use App\Models\City;
use App\Models\Property;
use App\Services\GooglePlacesService;
use App\Services\ImageWatermarkService;
use App\Services\PropertyService;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PropertyController extends Controller
{
    /**
     * Columns that are safe to load in the listing index.
     * Excludes `photos` to prevent MySQL sort buffer overflow when
     * property photos are stored as large base64 data URLs in the JSON column.
     *
     * @var list<string>
     */
    private const INDEX_COLUMNS = [
        'id', 'user_id', 'user_type', 'property_for', 'property_type',
        'title', 'slug', 'description', 'project_name', 'city', 'state', 'locality',
        'sub_locality', 'address', 'landmark', 'pincode', 'latitude', 'longitude',
        'bedrooms', 'bathrooms', 'balconies',
        'carpet_area', 'super_builtup_area', 'furnishing_status', 'floor_no',
        'total_floors', 'facing', 'construction_status', 'expected_price',
        'price_per_sqft', 'maintenance_charge', 'price_negotiable',
        'amenities', 'status', 'is_verified', 'is_featured', 'created_at', 'updated_at',
    ];

    /**
     * Display a listing of properties matching search & filter criteria.
     * Engineered for instant sub-50ms execution on crores of records.
     */
    public function index(Request $request, PropertyFilter $filter): JsonResponse
    {
        $perPage = min(max((int) $request->query('per_page', 15), 1), 50);
        $page = max((int) $request->query('page', 1), 1);
        $isSimple = $request->boolean('simple');

        // High-speed response cache lookup (sub-millisecond return on cached hits)
        $version = Cache::get('prop_version', 1);
        $cacheParams = $request->all();
        ksort($cacheParams);
        $responseCacheKey = 'prop_res_v4_'.$version.'_'.md5(json_encode($cacheParams));

        $cachedResponse = Cache::get($responseCacheKey);
        if ($cachedResponse !== null && is_array($cachedResponse)) {
            return response()->json($cachedResponse)
                ->header('Cache-Control', 'public, max-age=60, stale-while-revalidate=300')
                ->header('X-Cache', 'HIT');
        }

        $query = Property::query()
            ->select(self::INDEX_COLUMNS)
            ->with('user:id,name,email,mobile,country_code,role');

        $query = $filter->apply($query, $request->all());

        if ($isSimple) {
            $properties = $query->simplePaginate($perPage);
        } else {
            // Retrieve page items first utilizing covering composite indexes
            $items = $query->forPage($page, $perPage)->get();

            // Zero-query count optimization: If on page 1 and fewer than perPage results are returned,
            // the exact total count is known immediately with zero extra SQL queries!
            if ($page === 1 && $items->count() < $perPage) {
                $total = $items->count();
            } else {
                $countParams = $request->except(['page', 'sort', 'sort_by']);
                ksort($countParams);
                $countCacheKey = 'prop_cnt_v4_'.$version.'_'.md5(json_encode($countParams));

                $total = Cache::remember($countCacheKey, now()->addHours(2), function () use ($query) {
                    return (clone $query)->toBase()->getCountForPagination();
                });
            }

            $properties = new LengthAwarePaginator(
                $items,
                $total,
                $perPage,
                $page,
                ['path' => $request->url(), 'query' => $request->query()]
            );
        }

        $this->attachMedia($properties);

        $payload = $properties->toArray();
        Cache::put($responseCacheKey, $payload, now()->addMinutes(15));

        return response()->json($payload)
            ->header('Cache-Control', 'public, max-age=60, stale-while-revalidate=300')
            ->header('X-Cache', 'MISS');
    }

    /**
     * Display a specific property by ID or slug.
     */
    public function show(string $slugOrId): JsonResponse
    {
        $query = Property::with('user:id,name,email,mobile,country_code,role');

        if (ctype_digit($slugOrId)) {
            $property = $query->where('id', (int) $slugOrId)->first();
            if (! $property) {
                $property = Property::with('user:id,name,email,mobile,country_code,role')->where('slug', $slugOrId)->first();
            }
        } else {
            $property = $query->where('slug', $slugOrId)->first();
        }

        if (! $property) {
            return response()->json(['message' => 'Property not found.'], 404);
        }

        $cacheKey = "prop_show_{$property->id}";
        $data = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($property) {
            return ['property' => $property->toArray()];
        });

        if (! is_array($data) || empty($data['property']) || ! is_array($data['property'])) {
            Cache::forget($cacheKey);
            $data = ['property' => $property->toArray()];
        }

        return response()->json($data)->header('Cache-Control', 'public, max-age=120');
    }

    /**
     * Upload property photos to disk, apply brand watermark, and return their public URLs.
     */
    public function uploadPhotos(UploadPropertyPhotosRequest $request, ImageWatermarkService $watermarkService): JsonResponse
    {
        $urls = [];
        foreach ($request->file('photos') as $file) {
            $path = $watermarkService->applyAndStore($file, 'properties', 'public');
            $urls[] = Storage::url($path);
        }

        return response()->json([
            'status' => 'success',
            'urls' => $urls,
        ]);
    }

    /**
     * Upload property videos to disk and return their public URLs.
     */
    public function uploadVideos(UploadPropertyVideosRequest $request): JsonResponse
    {
        $urls = [];
        foreach ($request->file('videos') as $file) {
            $filename = Str::uuid().'.'.$file->getClientOriginalExtension();
            $path = $file->storeAs('properties/videos', $filename, 'public');
            $urls[] = Storage::url($path);
        }

        return response()->json([
            'status' => 'success',
            'urls' => $urls,
        ]);
    }

    /**
     * Store a newly created property listing.
     */
    public function store(StorePropertyRequest $request, PropertyService $propertyService): JsonResponse
    {
        $property = $propertyService->create($request->user(), $request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Property posted successfully! Your listing is now live on RealHem.',
            'property' => $property->load('user:id,name,email,mobile,country_code,role'),
        ], 201);
    }

    /**
     * Get properties posted by the authenticated user.
     */
    public function myProperties(Request $request): JsonResponse
    {
        $properties = Property::where('user_id', $request->user()->id)
            ->latest('id')
            ->paginate(20);

        return response()->json($properties);
    }

    /**
     * Update an existing property.
     */
    public function update(UpdatePropertyRequest $request, Property $property, PropertyService $propertyService): JsonResponse
    {
        $user = $request->user();
        if ($property->user_id !== $user->id && ! $user->isAdmin()) {
            return response()->json(['message' => 'Unauthorized. You do not own this property.'], 403);
        }

        $property = $propertyService->update($property, $request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Property updated successfully.',
            'property' => $property,
        ]);
    }

    /**
     * Quick status update (e.g. mark as sold, rented, or active).
     */
    public function updateStatus(UpdatePropertyStatusRequest $request, Property $property, PropertyService $propertyService): JsonResponse
    {
        $user = $request->user();
        if ($property->user_id !== $user->id && ! $user->isAdmin()) {
            return response()->json(['message' => 'Unauthorized. You do not own this property.'], 403);
        }

        $status = $request->validated('status');
        $property = $propertyService->updateStatus($property, $status);

        return response()->json([
            'status' => 'success',
            'message' => 'Property status updated to '.$status.'.',
            'property' => $property,
        ]);
    }

    /**
     * Remove the specified property listing from storage.
     */
    public function destroy(Request $request, Property $property, PropertyService $propertyService): JsonResponse
    {
        $user = $request->user();
        if ($property->user_id !== $user->id && ! $user->isAdmin()) {
            return response()->json(['message' => 'Unauthorized. You do not own this property.'], 403);
        }

        $propertyService->delete($property);

        return response()->json([
            'status' => 'success',
            'message' => 'Property listing deleted successfully.',
        ]);
    }

    /**
     * Real-time search suggestions for cities, localities, apartment/project names, and matching property titles.
     */
    public function suggestions(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));
        $city = trim((string) $request->query('city', ''));

        if (strlen($q) < 1) {
            return response()->json([]);
        }

        $cacheKey = 'sugg_'.md5(strtolower($city).'_'.strtolower($q));
        $data = Cache::remember($cacheKey, now()->addMinutes(15), function () use ($q, $city) {
            $suggestions = collect();

            // 1. Matching Cities
            $matchingCities = City::query()
                ->where('name', 'LIKE', "{$q}%")
                ->orWhere('name', 'LIKE', "%{$q}%")
                ->take(3)
                ->get(['id', 'name'])
                ->map(fn ($c) => [
                    'type' => 'city',
                    'category' => 'City',
                    'icon' => 'MapPin',
                    'title' => $c->name,
                    'subtitle' => 'City in India',
                    'city' => $c->name,
                    'keyword' => '',
                ]);
            $suggestions = $suggestions->concat($matchingCities);

            // 2. Matching Localities / Areas (DB Cache + Google Places API)
            $matchingLocalities = collect(GooglePlacesService::searchPlaces(
                query: $q,
                city: ! empty($city) ? $city : null,
                limit: 4
            ))->map(function ($loc) {
                $title = $loc['title'] ?? ($loc['name'] ?: $loc['locality']);

                return [
                    'type' => 'locality',
                    'category' => 'Locality / Area',
                    'icon' => 'MapPin',
                    'title' => $title,
                    'subtitle' => $loc['display'] ?: ($loc['city'] ? "Locality in {$loc['city']}" : 'Locality'),
                    'city' => $loc['city'],
                    'keyword' => $title,
                    'locality' => $loc['locality'],
                    'sub_locality' => $loc['sub_locality'],
                    'pincode' => $loc['pincode'],
                    'latitude' => $loc['latitude'],
                    'longitude' => $loc['longitude'],
                    'cached' => $loc['cached'],
                    'provider' => $loc['provider'],
                ];
            });
            $suggestions = $suggestions->concat($matchingLocalities);

            // 3. Matching Projects / Societies / Apartments (Google Places API + DB Cache + Listings)
            $googleProjects = collect(GooglePlacesService::searchProjects(
                query: $q,
                city: ! empty($city) ? $city : null,
                limit: 5
            ))->map(function ($proj) {
                $name = $proj['name'] ?: $proj['title'];

                return [
                    'type' => 'project',
                    'category' => 'Apartment / Project',
                    'icon' => 'Building2',
                    'title' => $name,
                    'subtitle' => $proj['display'] ?: implode(', ', array_filter([$proj['locality'] ?? null, $proj['city'] ?? null])),
                    'city' => $proj['city'] ?? null,
                    'keyword' => $name,
                    'locality' => $proj['locality'] ?? null,
                    'sub_locality' => $proj['sub_locality'] ?? null,
                    'pincode' => $proj['pincode'] ?? null,
                    'latitude' => $proj['latitude'] ?? null,
                    'longitude' => $proj['longitude'] ?? null,
                    'cached' => $proj['cached'] ?? false,
                    'provider' => $proj['provider'] ?? 'google_places_new',
                ];
            });

            $projQuery = Property::query()
                ->whereNotNull('project_name')
                ->where('project_name', '!=', '')
                ->where('project_name', 'LIKE', "%{$q}%");
            if (! empty($city)) {
                $projQuery->where('city', $city);
            }
            $dbProjects = $projQuery
                ->select(['project_name', 'locality', 'sub_locality', 'city', 'pincode', 'latitude', 'longitude'])
                ->distinct()
                ->take(4)
                ->get()
                ->map(fn ($prop) => [
                    'type' => 'project',
                    'category' => 'Apartment / Project',
                    'icon' => 'Building2',
                    'title' => $prop->project_name,
                    'subtitle' => implode(', ', array_filter([$prop->locality, $prop->city])),
                    'city' => $prop->city,
                    'keyword' => $prop->project_name,
                    'locality' => $prop->locality,
                    'sub_locality' => $prop->sub_locality,
                    'pincode' => $prop->pincode,
                    'latitude' => (float) $prop->latitude,
                    'longitude' => (float) $prop->longitude,
                    'cached' => true,
                    'provider' => 'database',
                ]);

            $matchingProjects = $googleProjects->concat($dbProjects)->unique(function ($item) {
                return strtolower(trim(($item['title'] ?? '').' '.($item['city'] ?? '')));
            })->values()->take(5);

            $suggestions = $suggestions->concat($matchingProjects);

            // 4. Matching Property Titles / Direct Listings
            $propQuery = Property::query()
                ->where(function ($query) use ($q) {
                    $query->where('title', 'LIKE', "%{$q}%")
                        ->orWhere('locality', 'LIKE', "%{$q}%")
                        ->orWhere('sub_locality', 'LIKE', "%{$q}%");
                });
            if (! empty($city)) {
                $propQuery->where('city', $city);
            }
            $matchingProperties = $propQuery
                ->select(['id', 'title', 'slug', 'locality', 'city', 'expected_price', 'bedrooms', 'property_type'])
                ->take(4)
                ->get()
                ->map(function ($prop) {
                    $formattedPrice = $prop->expected_price ? '₹ '.(
                        $prop->expected_price >= 10000000
                            ? round($prop->expected_price / 10000000, 2).' Cr'
                            : round($prop->expected_price / 100000, 2).' Lac'
                    ) : '';

                    return [
                        'type' => 'property',
                        'category' => 'Property Listing',
                        'icon' => 'Home',
                        'title' => $prop->title,
                        'subtitle' => implode(' • ', array_filter([$prop->locality.', '.$prop->city, $formattedPrice])),
                        'city' => $prop->city,
                        'keyword' => $prop->title,
                        'slug' => $prop->slug,
                        'id' => $prop->id,
                    ];
                });
            $suggestions = $suggestions->concat($matchingProperties);

            return $suggestions->values()->take(10)->all();
        });

        return response()->json($data)->header('Cache-Control', 'public, max-age=300');
    }

    /**
     * Attach photos and videos from a separate lightweight query only for requested page items.
     * Prevents loading heavy base64 strings during initial sorting queries.
     *
     * @param  Paginator|LengthAwarePaginator  $paginator
     */
    protected function attachMedia(mixed $paginator): void
    {
        $ids = $paginator->pluck('id');
        if ($ids->isEmpty()) {
            return;
        }

        $mediaMap = Property::query()
            ->whereIn('id', $ids)
            ->select('id', 'photos', 'videos')
            ->get()
            ->keyBy('id');

        $paginator->getCollection()->transform(function ($property) use ($mediaMap) {
            $property->photos = $mediaMap[$property->id]->photos ?? [];
            $property->videos = $mediaMap[$property->id]->videos ?? [];

            return $property;
        });
    }
}
