<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\UserActivity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserActivityController extends Controller
{
    /**
     * Get the authenticated user's recent activities.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = $user->activities()
            ->with(['property:id,title,slug,city,locality,expected_price,property_for,property_type,photos,status'])
            ->latest();

        if ($request->filled('type')) {
            $query->where('activity_type', $request->query('type'));
        }

        $activities = $query->paginate($request->integer('per_page', 20));

        return response()->json($activities);
    }

    /**
     * Store a new activity for the authenticated user.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'activity_type' => ['nullable', 'string', 'max:50'],
            'property_id' => ['nullable', 'integer', 'exists:properties,id'],
            'metadata' => ['nullable', 'array'],
        ]);

        $user = $request->user();
        $activityType = $validated['activity_type'] ?? 'view_property';
        $propertyId = $validated['property_id'] ?? null;

        // Debounce identical property views within the last 60 seconds
        if ($propertyId && $activityType === 'view_property') {
            $recent = UserActivity::where('user_id', $user->id)
                ->where('activity_type', $activityType)
                ->where('property_id', $propertyId)
                ->where('created_at', '>=', now()->subSeconds(60))
                ->first();

            if ($recent) {
                $recent->touch();

                return response()->json([
                    'status' => 'success',
                    'message' => 'Activity refreshed.',
                    'activity' => $recent,
                ], 200);
            }
        }

        $activity = UserActivity::create([
            'user_id' => $user->id,
            'activity_type' => $activityType,
            'property_id' => $propertyId,
            'metadata' => $validated['metadata'] ?? null,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Activity logged.',
            'activity' => $activity,
        ], 201);
    }

    /**
     * Sync guest activities stored in local browser upon login.
     */
    public function sync(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'activities' => ['required', 'array', 'max:50'],
            'activities.*.property_id' => ['nullable', 'integer'],
            'activities.*.activity_type' => ['nullable', 'string', 'max:50'],
            'activities.*.metadata' => ['nullable', 'array'],
            'activities.*.created_at' => ['nullable', 'string'],
        ]);

        $user = $request->user();
        $syncedCount = 0;

        foreach ($validated['activities'] as $item) {
            $propId = $item['property_id'] ?? null;
            $type = $item['activity_type'] ?? 'view_property';

            if ($propId && ! Property::where('id', $propId)->exists()) {
                continue;
            }

            // Avoid duplicating existing record
            $exists = UserActivity::where('user_id', $user->id)
                ->where('activity_type', $type)
                ->where('property_id', $propId)
                ->exists();

            if (! $exists) {
                UserActivity::create([
                    'user_id' => $user->id,
                    'activity_type' => $type,
                    'property_id' => $propId,
                    'metadata' => $item['metadata'] ?? null,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);
                $syncedCount++;
            }
        }

        return response()->json([
            'status' => 'success',
            'synced' => $syncedCount,
        ]);
    }
}
