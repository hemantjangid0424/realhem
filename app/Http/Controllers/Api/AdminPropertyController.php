<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Property;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;

class AdminPropertyController extends Controller
{
    /**
     * Columns loaded for index view.
     * Includes all essential listing attributes.
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
     * Check administrative privileges for all actions.
     */
    protected function authorizeAdmin(Request $request): ?JsonResponse
    {
        if (! $request->user() || ! $request->user()->isAdmin()) {
            return response()->json([
                'message' => 'Access denied. Administrative privileges are required.',
            ], 403);
        }

        return null;
    }

    /**
     * Get paginated properties list with search, status filters, and summary metrics.
     */
    public function index(Request $request): JsonResponse
    {
        if ($deny = $this->authorizeAdmin($request)) {
            return $deny;
        }

        // Summary counts for tabs and badges (cached to protect database at scale)
        $counts = Cache::remember('admin_property_counts', 60, function () {
            return [
                'total' => Property::count(),
                'pending' => Property::whereIn('status', ['pending', 'pending_approval'])->count(),
                'active' => Property::where('status', 'active')->count(),
                'rejected' => Property::where('status', 'rejected')->count(),
                'inactive' => Property::whereIn('status', ['inactive', 'sold', 'rented'])->count(),
                'verified' => Property::where('is_verified', true)->count(),
                'featured' => Property::where('is_featured', true)->count(),
            ];
        });

        $query = Property::query()
            ->select(self::INDEX_COLUMNS)
            ->with('user:id,name,email,mobile,country_code,role');

        // Status Filter
        $status = strtolower(trim((string) $request->query('status', 'all')));
        if ($status !== 'all' && ! empty($status)) {
            if ($status === 'pending') {
                $query->whereIn('status', ['pending', 'pending_approval']);
            } elseif ($status === 'inactive') {
                $query->whereIn('status', ['inactive', 'sold', 'rented']);
            } else {
                $query->where('status', $status);
            }
        }

        // Verification Filter
        if ($request->has('is_verified') && $request->query('is_verified') !== 'all') {
            $query->where('is_verified', $request->boolean('is_verified'));
        }

        // Featured Filter
        if ($request->has('is_featured') && $request->query('is_featured') !== 'all') {
            $query->where('is_featured', $request->boolean('is_featured'));
        }

        // Property For (Intent) Filter: Sell, Rent, PG
        if ($request->filled('property_for') && $request->query('property_for') !== 'all') {
            $query->where('property_for', $request->query('property_for'));
        }

        // City Filter
        if ($request->filled('city') && $request->query('city') !== 'all') {
            $query->where('city', $request->query('city'));
        }

        // Keyword Search (exact ID seek if numeric/#ID, or text search)
        if ($request->filled('search')) {
            $rawSearch = trim((string) $request->query('search'));

            if (preg_match('/^#?(\d+)$/', $rawSearch, $matches)) {
                // Instant O(1) primary key lookup
                $query->where('id', (int) $matches[1]);
            } else {
                $search = '%'.$rawSearch.'%';
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', $search)
                        ->orWhere('project_name', 'like', $search)
                        ->orWhere('locality', 'like', $search)
                        ->orWhere('sub_locality', 'like', $search)
                        ->orWhere('city', 'like', $search)
                        ->orWhere('pincode', 'like', $search)
                        ->orWhere('slug', 'like', $search)
                        ->orWhereHas('user', function ($uq) use ($search) {
                            $uq->where('name', 'like', $search)
                                ->orWhere('email', 'like', $search)
                                ->orWhere('mobile', 'like', $search);
                        });
                });
            }
        }

        // Sorting
        $sort = $request->query('sort', 'latest');
        match ($sort) {
            'oldest' => $query->orderBy('id', 'asc'),
            'price_high' => $query->orderBy('expected_price', 'desc'),
            'price_low' => $query->orderBy('expected_price', 'asc'),
            default => $query->orderBy('id', 'desc'),
        };

        $perPage = min((int) $request->query('per_page', 15), 50);
        $paginator = $query->paginate($perPage);

        // Safely load photos for the current page records without MySQL sort buffer overflow
        $ids = $paginator->pluck('id');
        $photoMap = Property::query()
            ->whereIn('id', $ids)
            ->select('id', 'photos')
            ->get()
            ->keyBy('id');

        $paginator->getCollection()->transform(function ($property) use ($photoMap) {
            $property->photos = $photoMap[$property->id]->photos ?? [];

            return $property;
        });

        return response()->json([
            'counts' => $counts,
            'properties' => $paginator,
        ]);
    }

    /**
     * Get single property detail with complete owner information and photos for review.
     */
    public function show(Request $request, Property $property): JsonResponse
    {
        if ($deny = $this->authorizeAdmin($request)) {
            return $deny;
        }

        $property->load('user:id,name,email,mobile,country_code,role');

        return response()->json([
            'success' => true,
            'property' => $property,
        ]);
    }

    /**
     * Approve a property, setting status to active and optionally marking it verified.
     */
    public function approve(Request $request, Property $property): JsonResponse
    {
        if ($deny = $this->authorizeAdmin($request)) {
            return $deny;
        }

        $verify = $request->boolean('verify', true);

        $property->status = 'active';
        if ($verify) {
            $property->is_verified = true;
        }
        $property->save();
        $property->load('user:id,name,email,mobile,country_code,role');
        $this->clearCountsCache();

        return response()->json([
            'success' => true,
            'message' => 'Property has been approved and published.',
            'property' => $property,
        ]);
    }

    /**
     * Reject a property listing.
     */
    public function reject(Request $request, Property $property): JsonResponse
    {
        if ($deny = $this->authorizeAdmin($request)) {
            return $deny;
        }

        $property->status = 'rejected';
        $property->save();
        $property->load('user:id,name,email,mobile,country_code,role');
        $this->clearCountsCache();

        return response()->json([
            'success' => true,
            'message' => 'Property listing has been marked as rejected.',
            'property' => $property,
        ]);
    }

    /**
     * Update property moderation status directly.
     */
    public function updateStatus(Request $request, Property $property): JsonResponse
    {
        if ($deny = $this->authorizeAdmin($request)) {
            return $deny;
        }

        $validated = $request->validate([
            'status' => ['required', 'string', Rule::in(['active', 'pending', 'pending_approval', 'rejected', 'inactive', 'sold', 'rented'])],
        ]);

        $property->status = $validated['status'];
        $property->save();
        $property->load('user:id,name,email,mobile,country_code,role');
        $this->clearCountsCache();

        return response()->json([
            'success' => true,
            'message' => "Property status updated to '{$property->status}'.",
            'property' => $property,
        ]);
    }

    /**
     * Toggle verified badge on the property.
     */
    public function toggleVerified(Request $request, Property $property): JsonResponse
    {
        if ($deny = $this->authorizeAdmin($request)) {
            return $deny;
        }

        $property->is_verified = ! $property->is_verified;
        $property->save();
        $property->load('user:id,name,email,mobile,country_code,role');
        $this->clearCountsCache();

        $statusMsg = $property->is_verified ? 'marked as Verified' : 'unmarked from Verified';

        return response()->json([
            'success' => true,
            'message' => "Property is now {$statusMsg}.",
            'property' => $property,
        ]);
    }

    /**
     * Toggle featured badge on the property.
     */
    public function toggleFeatured(Request $request, Property $property): JsonResponse
    {
        if ($deny = $this->authorizeAdmin($request)) {
            return $deny;
        }

        $property->is_featured = ! $property->is_featured;
        $property->save();
        $property->load('user:id,name,email,mobile,country_code,role');
        $this->clearCountsCache();

        $statusMsg = $property->is_featured ? 'marked as Featured' : 'unmarked from Featured';

        return response()->json([
            'success' => true,
            'message' => "Property is now {$statusMsg}.",
            'property' => $property,
        ]);
    }

    /**
     * Update property details directly from Admin.
     */
    public function update(Request $request, Property $property): JsonResponse
    {
        if ($deny = $this->authorizeAdmin($request)) {
            return $deny;
        }

        $validated = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'project_name' => ['nullable', 'string', 'max:255'],
            'property_for' => ['sometimes', 'required', 'string', Rule::in(['Sell', 'Rent', 'PG'])],
            'property_type' => ['sometimes', 'required', 'string', 'max:100'],
            'city' => ['sometimes', 'required', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'locality' => ['sometimes', 'required', 'string', 'max:150'],
            'sub_locality' => ['nullable', 'string', 'max:150'],
            'address' => ['nullable', 'string'],
            'landmark' => ['nullable', 'string', 'max:255'],
            'pincode' => ['nullable', 'string', 'max:10'],
            'bedrooms' => ['nullable', 'integer', 'min:0', 'max:20'],
            'bathrooms' => ['nullable', 'integer', 'min:0', 'max:20'],
            'balconies' => ['nullable', 'integer', 'min:0', 'max:10'],
            'carpet_area' => ['sometimes', 'required', 'numeric', 'min:1'],
            'super_builtup_area' => ['nullable', 'numeric', 'min:0'],
            'expected_price' => ['sometimes', 'required', 'numeric', 'min:1'],
            'price_per_sqft' => ['nullable', 'numeric', 'min:0'],
            'maintenance_charge' => ['nullable', 'numeric', 'min:0'],
            'price_negotiable' => ['nullable', 'boolean'],
            'furnishing_status' => ['nullable', 'string', 'max:50'],
            'floor_no' => ['nullable', 'string', 'max:30'],
            'total_floors' => ['nullable', 'integer', 'min:0', 'max:200'],
            'facing' => ['nullable', 'string', 'max:50'],
            'construction_status' => ['nullable', 'string', 'max:50'],
            'status' => ['nullable', 'string', Rule::in(['active', 'pending', 'pending_approval', 'rejected', 'inactive', 'sold', 'rented'])],
            'is_verified' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
        ]);

        $property->fill($validated);
        $property->save();
        $property->load('user:id,name,email,mobile,country_code,role');
        $this->clearCountsCache();

        return response()->json([
            'success' => true,
            'message' => 'Property updated successfully.',
            'property' => $property,
        ]);
    }

    /**
     * Permanently delete a property from the portal.
     */
    public function destroy(Request $request, Property $property): JsonResponse
    {
        if ($deny = $this->authorizeAdmin($request)) {
            return $deny;
        }

        $property->delete();
        $this->clearCountsCache();

        return response()->json([
            'success' => true,
            'message' => 'Property deleted successfully.',
        ]);
    }

    /**
     * Clear cached admin count statistics.
     */
    private function clearCountsCache(): void
    {
        Cache::forget('admin_property_counts');
    }
}
