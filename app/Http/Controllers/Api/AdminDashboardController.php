<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    /**
     * Get platform statistics for the Admin SPA.
     */
    public function stats(): JsonResponse
    {
        $stats = [
            'total_users' => User::count(),
            'total_admins' => User::where('role', 'admin')->count(),
            'total_agents' => User::where('role', 'agent')->count(),
            'total_owners' => User::where('role', 'owner')->count(),
            'total_buyers' => User::where('role', 'user')->count(),
            'total_properties' => 0,
            'pending_verifications' => 0,
            'total_inquiries' => 0,
            'cities_count' => 8,
        ];

        $recentUsers = User::latest()->take(6)->get(['id', 'name', 'email', 'role', 'created_at']);

        return response()->json([
            'stats' => $stats,
            'recent_users' => $recentUsers,
        ]);
    }

    /**
     * Get all users for admin management.
     */
    public function users(Request $request): JsonResponse
    {
        $role = $request->query('role');

        $query = User::latest();

        if ($role && in_array($role, ['admin', 'agent', 'owner', 'user'])) {
            $query->where('role', $role);
        }

        $users = $query->paginate(15);

        return response()->json($users);
    }
}
