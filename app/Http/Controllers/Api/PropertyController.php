<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Property;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PropertyController extends Controller
{
    /**
     * Columns that are safe to load in the listing index.
     * Excludes `photos` to prevent MySQL sort buffer overflow when
     * property photos are stored as large base64 data URLs in the JSON column.
     */
    private const INDEX_COLUMNS = [
        'id', 'user_id', 'user_type', 'property_for', 'property_type',
        'title', 'slug', 'description', 'project_name', 'city', 'locality',
        'sub_locality', 'landmark', 'bedrooms', 'bathrooms', 'balconies',
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

        // Property For (Sell, Rent, PG)
        if ($request->filled('property_for')) {
            $query->where('property_for', $request->query('property_for'));
        } elseif ($request->filled('type')) {
            $type = strtolower($request->query('type'));
            if ($type === 'rent') {
                $query->where('property_for', 'Rent');
            } elseif ($type === 'buy' || $type === 'sale') {
                $query->where('property_for', 'Sell');
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

        // Search query (keyword in title, locality, project_name, or description)
        if ($request->filled('search')) {
            $search = '%'.$request->query('search').'%';
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', $search)
                    ->orWhere('locality', 'like', $search)
                    ->orWhere('project_name', 'like', $search)
                    ->orWhere('city', 'like', $search);
            });
        }

        // Locality filter
        if ($request->filled('locality')) {
            $query->where('locality', 'like', '%'.$request->query('locality').'%');
        }

        // Verified only
        if ($request->boolean('verified_only')) {
            $query->where('is_verified', true);
        }

        // Add photos separately after sorting to avoid sort buffer issues.
        // We fetch the IDs first (lightweight), then load photos for those IDs.
        $properties = $query->orderBy('id', 'desc')->paginate(15);

        // Attach photos from a separate lightweight query
        $ids = $properties->pluck('id');
        $photoMap = Property::query()
            ->whereIn('id', $ids)
            ->select('id', 'photos')
            ->get()
            ->keyBy('id');

        $properties->getCollection()->transform(function ($property) use ($photoMap) {
            $property->photos = $photoMap[$property->id]->photos ?? [];

            return $property;
        });

        return response()->json($properties);
    }

    /**
     * Display a specific property.
     */
    public function show(Property $property): JsonResponse
    {
        $property->load('user:id,name,email,mobile,country_code,role');

        return response()->json([
            'property' => $property,
        ]);
    }

    /**
     * Upload property photos to disk and return their public URLs.
     * Accepts multipart file uploads (jpg, jpeg, webp), max 3 files, max 5 MB each.
     */
    public function uploadPhotos(Request $request): JsonResponse
    {
        $request->validate([
            'photos' => ['required', 'array', 'min:1', 'max:10'],
            'photos.*' => ['required', 'file', 'mimes:jpg,jpeg,webp', 'max:5120'],
        ]);

        $urls = [];
        foreach ($request->file('photos') as $file) {
            $filename = Str::uuid().'.'.$file->getClientOriginalExtension();
            $path = $file->storeAs('properties', $filename, 'public');
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
            'property_for' => ['required', 'string', 'in:Sell,Rent,PG'],
            'property_type' => ['required', 'string', 'max:100'],
            'city' => ['required', 'string', 'max:100'],
            'locality' => ['required', 'string', 'max:150'],
            'sub_locality' => ['nullable', 'string', 'max:150'],
            'project_name' => ['nullable', 'string', 'max:150'],
            'address' => ['nullable', 'string'],
            'landmark' => ['nullable', 'string', 'max:150'],

            // Step 2: Property Profile & Layout
            'bedrooms' => ['required', 'integer', 'min:1', 'max:20'],
            'bathrooms' => ['required', 'integer', 'min:1', 'max:20'],
            'balconies' => ['nullable', 'integer', 'min:0', 'max:10'],
            'carpet_area' => ['required', 'integer', 'min:50'],
            'super_builtup_area' => ['nullable', 'integer'],
            'furnishing_status' => ['required', 'string', 'in:Unfurnished,Semi-Furnished,Furnished'],
            'floor_no' => ['nullable', 'string', 'max:50'],
            'total_floors' => ['nullable', 'integer', 'min:1', 'max:150'],
            'facing' => ['nullable', 'string', 'max:50'],
            'construction_status' => ['required', 'string', 'in:Ready to Move,Under Construction,New Launch'],

            // Step 3: Photos & Amenities
            // Photos must be public storage paths or URLs (not base64 blobs)
            'amenities' => ['nullable', 'array'],
            'amenities.*' => ['string', 'max:100'],
            'photos' => ['nullable', 'array', 'max:10'],
            'photos.*' => ['string', 'max:512'],

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
            $for = $validated['property_for'] === 'Sell' ? 'Sale' : $validated['property_for'];
            $title = "{$validated['bedrooms']} BHK {$validated['property_type']} for {$for} in {$validated['locality']}, {$validated['city']}";
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
            'locality' => $validated['locality'],
            'sub_locality' => $validated['sub_locality'] ?? null,
            'address' => $validated['address'] ?? null,
            'landmark' => $validated['landmark'] ?? null,
            'bedrooms' => (int) $validated['bedrooms'],
            'bathrooms' => (int) $validated['bathrooms'],
            'balconies' => isset($validated['balconies']) ? (int) $validated['balconies'] : 1,
            'carpet_area' => $carpetArea,
            'super_builtup_area' => isset($validated['super_builtup_area']) ? (int) $validated['super_builtup_area'] : (int) round($carpetArea * 1.25),
            'furnishing_status' => $validated['furnishing_status'],
            'floor_no' => $validated['floor_no'] ?? '1',
            'total_floors' => $validated['total_floors'] ?? 10,
            'facing' => $validated['facing'] ?? 'East',
            'construction_status' => $validated['construction_status'],
            'expected_price' => $expectedPrice,
            'price_per_sqft' => $pricePerSqft,
            'maintenance_charge' => isset($validated['maintenance_charge']) ? (int) $validated['maintenance_charge'] : 0,
            'price_negotiable' => (bool) ($validated['price_negotiable'] ?? false),
            'amenities' => $validated['amenities'] ?? [],
            'photos' => $validated['photos'] ?? [],
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
            'property_for' => ['required', 'string', 'in:Sell,Rent,PG'],
            'property_type' => ['required', 'string', 'max:100'],
            'city' => ['required', 'string', 'max:100'],
            'locality' => ['required', 'string', 'max:150'],
            'sub_locality' => ['nullable', 'string', 'max:150'],
            'project_name' => ['nullable', 'string', 'max:150'],
            'bedrooms' => ['required', 'integer', 'min:1', 'max:20'],
            'bathrooms' => ['required', 'integer', 'min:1', 'max:20'],
            'balconies' => ['nullable', 'integer', 'min:0', 'max:10'],
            'carpet_area' => ['required', 'integer', 'min:50'],
            'super_builtup_area' => ['nullable', 'integer'],
            'furnishing_status' => ['required', 'string'],
            'floor_no' => ['nullable', 'string', 'max:50'],
            'total_floors' => ['nullable', 'integer'],
            'facing' => ['nullable', 'string'],
            'construction_status' => ['required', 'string'],
            'expected_price' => ['required', 'numeric', 'min:1000'],
            'maintenance_charge' => ['nullable', 'numeric'],
            'price_negotiable' => ['nullable', 'boolean'],
            'description' => ['nullable', 'string'],
            'amenities' => ['nullable', 'array'],
            'photos' => ['nullable', 'array'],
            'status' => ['nullable', 'string', 'in:active,pending_approval,sold,rented'],
        ]);

        $carpetArea = (int) $validated['carpet_area'];
        $expectedPrice = (int) $validated['expected_price'];
        $pricePerSqft = $carpetArea > 0 ? (int) round($expectedPrice / $carpetArea) : null;

        $property->update(array_merge($validated, [
            'price_per_sqft' => $pricePerSqft,
        ]));

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

        $property->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Property listing deleted successfully.',
        ]);
    }
}
