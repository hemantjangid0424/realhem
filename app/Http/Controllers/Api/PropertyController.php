<?php

namespace App\Http\Controllers\Api;

use App\Enums\PropertyFor;
use App\Enums\PropertyType;
use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Locality;
use App\Models\Property;
use App\Services\GooglePlacesService;
use App\Services\ImageWatermarkService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PropertyController extends Controller
{
    /**
     * Columns that are safe to load in the listing index.
     * Excludes `photos` to prevent MySQL sort buffer overflow when
     * property photos are stored as large base64 data URLs in the JSON column.
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
     * Display a listing of properties.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Property::query()
            ->select(self::INDEX_COLUMNS)
            ->with('user:id,name,email,mobile,country_code,role');

        // City filter
        if ($request->filled('city')) {
            $query->where('city', $request->query('city'));
        }

        // Category / Intent / Property For filter
        $typeParam = strtolower(trim((string) ($request->query('type') ?? '')));
        $propertyForParam = $request->query('property_for');

        if ($typeParam === 'commercial') {
            $query->where(function ($q) {
                $q->where('property_type', 'LIKE', 'Commercial%')
                    ->orWhere('property_type', 'LIKE', '%Office%')
                    ->orWhere('property_type', 'LIKE', '%Shop%')
                    ->orWhere('property_type', 'LIKE', '%Showroom%');
            });
            if ($propertyForParam) {
                $pFor = PropertyFor::tryFromQuery($propertyForParam);
                if ($pFor && $pFor !== PropertyFor::Commercial) {
                    $query->where('property_for', $pFor->value);
                }
            }
        } elseif (in_array($typeParam, ['plots', 'plot', 'plots_land', 'land'], true)) {
            $query->where(function ($q) {
                $q->where('property_type', 'LIKE', '%Plot%')
                    ->orWhere('property_type', 'LIKE', '%Land%');
            });
            if ($propertyForParam) {
                $pFor = PropertyFor::tryFromQuery($propertyForParam);
                if ($pFor && $pFor !== PropertyFor::Commercial) {
                    $query->where('property_for', $pFor->value);
                }
            }
        } elseif (! empty($typeParam) && $typeParam !== 'all') {
            $propertyFor = PropertyFor::tryFromQuery($typeParam);
            if ($propertyFor && $propertyFor !== PropertyFor::Commercial) {
                $query->where('property_for', $propertyFor->value);
            }
        } elseif (! empty($propertyForParam)) {
            $propertyFor = PropertyFor::tryFromQuery($propertyForParam);
            if ($propertyFor && $propertyFor !== PropertyFor::Commercial) {
                $query->where('property_for', $propertyFor->value);
            }
        }

        // Specific property_type filter (single or comma-separated)
        if ($request->filled('property_type')) {
            $types = is_array($request->query('property_type'))
                ? $request->query('property_type')
                : explode(',', (string) $request->query('property_type'));
            $types = array_filter(array_map('trim', $types));
            if (! empty($types)) {
                $query->whereIn('property_type', $types);
            }
        }

        // Bedrooms filter (e.g. 1,2,3 or comma-separated)
        if ($request->filled('bhk')) {
            $bhks = is_array($request->query('bhk'))
                ? $request->query('bhk')
                : explode(',', $request->query('bhk'));
            $bhks = array_filter(array_map('intval', $bhks));
            if (! empty($bhks)) {
                $query->whereIn('bedrooms', $bhks);
            }
        }

        // Budget filters
        if ($request->filled('budget_min')) {
            $query->where('expected_price', '>=', (int) $request->query('budget_min'));
        }
        if ($request->filled('budget_max')) {
            $query->where('expected_price', '<=', (int) $request->query('budget_max'));
        }

        // Construction / Possession Status filter
        if ($request->filled('construction_status')) {
            $cStatus = strtolower(trim((string) $request->query('construction_status')));
            if ($cStatus === 'ready' || $cStatus === 'ready_to_move') {
                $query->where('construction_status', 'like', '%ready%');
            } elseif ($cStatus === 'under' || $cStatus === 'under_construction') {
                $query->where(function ($q) {
                    $q->where('construction_status', 'like', '%under%')
                        ->orWhere('construction_status', 'like', '%construction%');
                });
            }
        }

        // Quick tags filter (new_launch, owner, verified, under_construction, ready_to_move, with_photos)
        if ($request->filled('tags')) {
            $tags = is_array($request->query('tags'))
                ? $request->query('tags')
                : explode(',', (string) $request->query('tags'));
            $tags = array_filter(array_map('trim', $tags));

            foreach ($tags as $tag) {
                if ($tag === 'new_launch') {
                    $query->where(function ($q) {
                        $q->where('construction_status', 'like', '%new%')
                            ->orWhere('title', 'like', '%new launch%')
                            ->orWhere('description', 'like', '%new launch%');
                    });
                } elseif ($tag === 'owner') {
                    $query->where(function ($q) {
                        $q->where('user_type', 'owner')
                            ->orWhereHas('user', fn ($u) => $u->where('role', 'user'));
                    });
                } elseif ($tag === 'verified') {
                    $query->where('is_verified', true);
                } elseif ($tag === 'under_construction') {
                    $query->where(function ($q) {
                        $q->where('construction_status', 'like', '%under%')
                            ->orWhere('construction_status', 'like', '%construction%');
                    });
                } elseif ($tag === 'ready_to_move') {
                    $query->where('construction_status', 'like', '%ready%');
                } elseif ($tag === 'with_photos') {
                    $query->whereNotNull('photos')->whereRaw("photos != '[]' AND photos != 'null'");
                }
            }
        }

        // Search query (Uses MySQL FULLTEXT index when available for sub-millisecond execution on crores of records)
        if ($request->filled('search') || $request->filled('keyword')) {
            $rawSearch = trim((string) ($request->query('search') ?: $request->query('keyword')));
            $driver = Schema::getConnection()->getDriverName();

            if ($driver === 'mysql' && strlen($rawSearch) >= 3) {
                $sanitizedSearch = preg_replace('/[+\-><()~*\"@]+/', ' ', $rawSearch);
                $words = array_filter(explode(' ', trim((string) $sanitizedSearch)));
                if (! empty($words)) {
                    $booleanQuery = implode(' ', array_map(fn ($w) => '+'.$w.'*', $words));
                    $query->whereRaw('MATCH(title, project_name, locality) AGAINST(? IN BOOLEAN MODE)', [$booleanQuery]);
                } else {
                    $search = '%'.$rawSearch.'%';
                    $query->where(function ($q) use ($search) {
                        $q->where('title', 'like', $search)
                            ->orWhere('locality', 'like', $search)
                            ->orWhere('project_name', 'like', $search)
                            ->orWhere('city', 'like', $search);
                    });
                }
            } else {
                $search = '%'.$rawSearch.'%';
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', $search)
                        ->orWhere('locality', 'like', $search)
                        ->orWhere('sub_locality', 'like', $search)
                        ->orWhere('project_name', 'like', $search)
                        ->orWhere('city', 'like', $search)
                        ->orWhere('address', 'like', $search)
                        ->orWhere('property_type', 'like', $search);
                });
            }
        }

        // Locality filter (single or multiple nearby localities using indexed match)
        if ($request->filled('localities')) {
            $locList = is_array($request->query('localities'))
                ? $request->query('localities')
                : explode(',', (string) $request->query('localities'));
            $locList = array_values(array_filter(array_map('trim', $locList)));
            if (! empty($locList)) {
                $query->whereIn('locality', $locList);
            }
        } elseif ($request->filled('locality')) {
            $loc = trim((string) $request->query('locality'));
            $query->where('locality', $loc);
        }

        // Verified only
        if ($request->boolean('verified_only')) {
            $query->where('is_verified', true);
        }

        // Featured only
        if ($request->boolean('featured_only')) {
            $query->where('is_featured', true);
        }

        // Server-side dynamic sorting (uses composite indexes for instant retrieval)
        $sortBy = $request->query('sort') ?: $request->query('sort_by', 'relevance');
        match ($sortBy) {
            'price_low' => $query->orderBy('expected_price', 'asc')->orderBy('id', 'desc'),
            'price_high' => $query->orderBy('expected_price', 'desc')->orderBy('id', 'desc'),
            'newest' => $query->orderBy('id', 'desc'),
            default => $query->orderBy('is_featured', 'desc')->orderBy('id', 'desc'),
        };

        // Bounded pagination parameters
        $perPage = min(max((int) $request->query('per_page', 15), 1), 50);
        $page = max((int) $request->query('page', 1), 1);

        if ($request->boolean('simple')) {
            // simplePaginate runs zero COUNT(*) queries, ideal for infinite scroll over crores of rows
            $properties = $query->simplePaginate($perPage);
        } else {
            // Cache the total count for 5 minutes per unique filter combination to avoid expensive COUNT(*) on crores of records
            $countParams = $request->except(['page', 'sort', 'sort_by']);
            $cacheKey = 'prop_cnt_'.md5(json_encode($countParams));
            $total = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($query) {
                return (clone $query)->toBase()->getCountForPagination();
            });

            $items = $query->forPage($page, $perPage)->get();
            $properties = new LengthAwarePaginator(
                $items,
                $total,
                $perPage,
                $page,
                ['path' => $request->url(), 'query' => $request->query()]
            );
        }

        // Attach photos and videos from a separate lightweight query only for the requested page items
        $ids = $properties->pluck('id');
        if ($ids->isNotEmpty()) {
            $mediaMap = Property::query()
                ->whereIn('id', $ids)
                ->select('id', 'photos', 'videos')
                ->get()
                ->keyBy('id');

            $properties->getCollection()->transform(function ($property) use ($mediaMap) {
                $property->photos = $mediaMap[$property->id]->photos ?? [];
                $property->videos = $mediaMap[$property->id]->videos ?? [];

                return $property;
            });
        }

        return response()->json($properties)->header('Cache-Control', 'public, max-age=60');
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

        $id = $property->id;
        $cacheKey = "prop_show_{$id}";
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
     * Accepts multipart file uploads (jpg, jpeg, webp, png), max 10 files, max 5 MB each.
     */
    public function uploadPhotos(Request $request, ImageWatermarkService $watermarkService): JsonResponse
    {
        $request->validate([
            'photos' => ['required', 'array', 'min:1', 'max:10'],
            'photos.*' => ['required', 'file', 'mimes:jpg,jpeg,webp,png', 'max:5120'],
        ]);

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
     * Accepts multipart video file uploads (mp4, webm, mov, m4v), max 3 files, max 50 MB each.
     */
    public function uploadVideos(Request $request): JsonResponse
    {
        $request->validate([
            'videos' => ['required', 'array', 'min:1', 'max:3'],
            'videos.*' => ['required', 'file', 'mimetypes:video/mp4,video/webm,video/quicktime,video/x-m4v', 'max:51200'],
        ]);

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
     * Store a newly created property in storage.
     * Requires authenticated user.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            // Step 1: Basic & Location Details
            'user_type' => ['nullable', 'string', 'in:Owner,Agent,Builder'],
            'property_for' => ['required', Rule::enum(PropertyFor::class)],
            'property_type' => ['required', 'string', 'max:100'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'locality' => ['required', 'string', 'max:150'],
            'sub_locality' => ['nullable', 'string', 'max:150'],
            'project_name' => ['nullable', 'string', 'max:150'],
            'address' => ['nullable', 'string'],
            'landmark' => ['nullable', 'string', 'max:150'],
            'pincode' => ['nullable', 'string', 'max:10'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],

            // Step 2: Property Profile & Layout
            'bedrooms' => ['nullable', 'integer', 'min:0', 'max:20'],
            'bathrooms' => ['nullable', 'integer', 'min:0', 'max:20'],
            'balconies' => ['nullable', 'integer', 'min:0', 'max:10'],
            'carpet_area' => ['required', 'integer', 'min:10'],
            'super_builtup_area' => ['nullable', 'integer'],
            'furnishing_status' => ['nullable', 'string'],
            'floor_no' => ['nullable', 'string', 'max:50'],
            'total_floors' => ['nullable', 'integer', 'min:1', 'max:150'],
            'facing' => ['nullable', 'string', 'max:50'],
            'construction_status' => ['nullable', 'string', 'max:100'],

            // Step 3: Photos, Videos & Amenities
            'amenities' => ['nullable', 'array'],
            'amenities.*' => ['string', 'max:100'],
            'photos' => ['nullable', 'array', 'max:10'],
            'photos.*' => ['string', 'max:512'],
            'videos' => ['nullable', 'array', 'max:5'],
            'videos.*' => ['string', 'max:512'],

            // Step 4: Pricing & Finalize
            'expected_price' => ['required', 'numeric', 'min:1000'],
            'maintenance_charge' => ['nullable', 'numeric', 'min:0'],
            'price_negotiable' => ['nullable', 'boolean'],
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $user = $request->user();

        // Calculate price per sq.ft if not given
        $carpetArea = (int) $validated['carpet_area'];
        $expectedPrice = (int) $validated['expected_price'];
        $pricePerSqft = $carpetArea > 0 ? (int) round($expectedPrice / $carpetArea) : null;

        // Auto-generate title if missing
        $title = $validated['title'] ?? null;
        if (empty($title)) {
            $forEnum = PropertyFor::tryFrom($validated['property_for']) ?? PropertyFor::Sell;
            $forLabel = $forEnum->label();
            $bedrooms = (int) ($validated['bedrooms'] ?? 0);
            if (PropertyType::isResidentialType($validated['property_type']) && $bedrooms > 0) {
                $title = "{$bedrooms} BHK {$validated['property_type']} for {$forLabel} in {$validated['locality']}, {$validated['city']}";
            } else {
                $title = "{$carpetArea} sq.ft. {$validated['property_type']} for {$forLabel} in {$validated['locality']}, {$validated['city']}";
            }
        }

        // Determine user type
        $userType = $validated['user_type'] ?? null;
        if (empty($userType)) {
            $userType = match ($user->role) {
                'agent' => 'Agent',
                'builder' => 'Builder',
                default => 'Owner',
            };
        }

        // Resolve coordinates and pincode based on locality, project name, city, address
        $resolvedLocation = GooglePlacesService::resolve(
            city: $validated['city'],
            locality: $validated['locality'],
            projectName: $validated['project_name'] ?? null,
            subLocality: $validated['sub_locality'] ?? null,
            address: $validated['address'] ?? null,
            landmark: $validated['landmark'] ?? null,
            pincode: $validated['pincode'] ?? null,
            latitude: isset($validated['latitude']) ? (float) $validated['latitude'] : null,
            longitude: isset($validated['longitude']) ? (float) $validated['longitude'] : null,
        );

        // Create the property
        $property = Property::create([
            'user_id' => $user->id,
            'user_type' => $userType,
            'property_for' => $validated['property_for'],
            'property_type' => $validated['property_type'],
            'title' => $title,
            'description' => $validated['description'] ?? null,
            'project_name' => $validated['project_name'] ?? null,
            'city' => $validated['city'],
            'state' => $validated['state'] ?? ($resolvedLocation['state'] ?? null),
            'locality' => $validated['locality'],
            'sub_locality' => $validated['sub_locality'] ?? null,
            'address' => $validated['address'] ?? null,
            'landmark' => $validated['landmark'] ?? null,
            'pincode' => $resolvedLocation['pincode'],
            'latitude' => $resolvedLocation['latitude'],
            'longitude' => $resolvedLocation['longitude'],
            'bedrooms' => (int) ($validated['bedrooms'] ?? 0),
            'bathrooms' => (int) ($validated['bathrooms'] ?? 0),
            'balconies' => isset($validated['balconies']) ? (int) $validated['balconies'] : 0,
            'carpet_area' => $carpetArea,
            'super_builtup_area' => isset($validated['super_builtup_area']) ? (int) $validated['super_builtup_area'] : (int) round($carpetArea * 1.25),
            'furnishing_status' => $validated['furnishing_status'] ?? 'Unfurnished',
            'floor_no' => $validated['floor_no'] ?? '1',
            'total_floors' => $validated['total_floors'] ?? 1,
            'facing' => $validated['facing'] ?? 'East',
            'construction_status' => $validated['construction_status'] ?? 'Ready to Move',
            'expected_price' => $expectedPrice,
            'price_per_sqft' => $pricePerSqft,
            'maintenance_charge' => isset($validated['maintenance_charge']) ? (int) $validated['maintenance_charge'] : 0,
            'price_negotiable' => (bool) ($validated['price_negotiable'] ?? false),
            'amenities' => $validated['amenities'] ?? [],
            'photos' => $validated['photos'] ?? [],
            'videos' => $validated['videos'] ?? [],
            'status' => 'active',
            'is_verified' => true,
            'is_featured' => false,
        ]);

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
            ->orderBy('id', 'desc')
            ->paginate(20);

        return response()->json($properties);
    }

    /**
     * Update an existing property.
     */
    public function update(Request $request, Property $property): JsonResponse
    {
        $user = $request->user();
        if ($property->user_id !== $user->id && ! $user->isAdmin()) {
            return response()->json(['message' => 'Unauthorized. You do not own this property.'], 403);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'property_for' => ['required', Rule::enum(PropertyFor::class)],
            'property_type' => ['required', 'string', 'max:100'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'locality' => ['required', 'string', 'max:150'],
            'sub_locality' => ['nullable', 'string', 'max:150'],
            'project_name' => ['nullable', 'string', 'max:150'],
            'address' => ['nullable', 'string'],
            'landmark' => ['nullable', 'string', 'max:150'],
            'pincode' => ['nullable', 'string', 'max:10'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'bedrooms' => ['nullable', 'integer', 'min:0', 'max:20'],
            'bathrooms' => ['nullable', 'integer', 'min:0', 'max:20'],
            'balconies' => ['nullable', 'integer', 'min:0', 'max:10'],
            'carpet_area' => ['required', 'integer', 'min:10'],
            'super_builtup_area' => ['nullable', 'integer'],
            'furnishing_status' => ['nullable', 'string'],
            'floor_no' => ['nullable', 'string', 'max:50'],
            'total_floors' => ['nullable', 'integer'],
            'facing' => ['nullable', 'string'],
            'construction_status' => ['nullable', 'string'],
            'expected_price' => ['required', 'numeric', 'min:1000'],
            'maintenance_charge' => ['nullable', 'numeric'],
            'price_negotiable' => ['nullable', 'boolean'],
            'description' => ['nullable', 'string'],
            'amenities' => ['nullable', 'array'],
            'photos' => ['nullable', 'array'],
            'videos' => ['nullable', 'array'],
            'status' => ['nullable', 'string', 'in:active,pending_approval,sold,rented'],
        ]);

        $carpetArea = (int) $validated['carpet_area'];
        $expectedPrice = (int) $validated['expected_price'];
        $pricePerSqft = $carpetArea > 0 ? (int) round($expectedPrice / $carpetArea) : null;

        $localityChanged = $validated['locality'] !== $property->locality || $validated['city'] !== $property->city;

        $resolvedLocation = GooglePlacesService::resolve(
            city: $validated['city'],
            locality: $validated['locality'],
            projectName: $validated['project_name'] ?? ($localityChanged ? null : $property->project_name),
            subLocality: $validated['sub_locality'] ?? ($localityChanged ? null : $property->sub_locality),
            address: $validated['address'] ?? ($localityChanged ? null : $property->address),
            landmark: $validated['landmark'] ?? ($localityChanged ? null : $property->landmark),
            pincode: $validated['pincode'] ?? ($localityChanged ? null : $property->pincode),
            latitude: isset($validated['latitude']) ? (float) $validated['latitude'] : ($localityChanged ? null : $property->latitude),
            longitude: isset($validated['longitude']) ? (float) $validated['longitude'] : ($localityChanged ? null : $property->longitude),
        );

        $property->update(array_merge($validated, [
            'price_per_sqft' => $pricePerSqft,
            'state' => $validated['state'] ?? ($resolvedLocation['state'] ?? $property->state),
            'pincode' => $resolvedLocation['pincode'],
            'latitude' => $resolvedLocation['latitude'],
            'longitude' => $resolvedLocation['longitude'],
        ]));

        Cache::forget("prop_show_{$property->id}");

        return response()->json([
            'status' => 'success',
            'message' => 'Property updated successfully.',
            'property' => $property->fresh(),
        ]);
    }

    /**
     * Quick status update (e.g. mark as sold, rented, or active).
     */
    public function updateStatus(Request $request, Property $property): JsonResponse
    {
        $user = $request->user();
        if ($property->user_id !== $user->id && ! $user->isAdmin()) {
            return response()->json(['message' => 'Unauthorized. You do not own this property.'], 403);
        }

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:active,pending_approval,sold,rented'],
        ]);

        $property->update(['status' => $validated['status']]);

        Cache::forget("prop_show_{$property->id}");

        return response()->json([
            'status' => 'success',
            'message' => 'Property status updated to '.$validated['status'].'.',
            'property' => $property,
        ]);
    }

    /**
     * Remove the specified property from storage.
     */
    public function destroy(Request $request, Property $property): JsonResponse
    {
        $user = $request->user();
        if ($property->user_id !== $user->id && ! $user->isAdmin()) {
            return response()->json(['message' => 'Unauthorized. You do not own this property.'], 403);
        }

        Cache::forget("prop_show_{$property->id}");

        $property->delete();

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
                ->map(function ($c) {
                    return [
                        'type' => 'city',
                        'category' => 'City',
                        'icon' => '📍',
                        'title' => $c->name,
                        'subtitle' => 'City in India',
                        'city' => $c->name,
                        'keyword' => '',
                    ];
                });
            $suggestions = $suggestions->concat($matchingCities);

            // 2. Matching Localities / Areas (powered by DB Cache + Google Places API)
            $matchingLocalities = collect(GooglePlacesService::searchPlaces(
                query: $q,
                city: ! empty($city) ? $city : null,
                limit: 4
            ))->map(function ($loc) {
                $title = $loc['title'] ?? ($loc['name'] ?: $loc['locality']);

                return [
                    'type' => 'locality',
                    'category' => 'Locality / Area',
                    'icon' => '🏘️',
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

            // 3. Matching Projects / Societies / Apartments / Buildings (Google Places API + DB Cache + Listings)
            $googleProjects = collect(GooglePlacesService::searchProjects(
                query: $q,
                city: ! empty($city) ? $city : null,
                limit: 5
            ))->map(function ($proj) {
                $name = $proj['name'] ?: $proj['title'];

                return [
                    'type' => 'project',
                    'category' => 'Apartment / Project',
                    'icon' => '🏢',
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
                ->map(function ($prop) {
                    return [
                        'type' => 'project',
                        'category' => 'Apartment / Project',
                        'icon' => '🏢',
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
                    ];
                });

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
                        'icon' => '🏠',
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
}
