<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Job\StoreJobPostingRequest;
use App\Http\Requests\Job\UpdateJobPostingRequest;
use App\Models\JobPosting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class JobPostingController extends Controller
{
    /**
     * Public list of active job postings.
     */
    public function index(): JsonResponse
    {
        $jobs = JobPosting::active()
            ->orderBy('sort_order')
            ->latest('id')
            ->get();

        $departments = JobPosting::active()
            ->distinct()
            ->pluck('department')
            ->prepend('All')
            ->values();

        return response()->json([
            'success' => true,
            'data' => $jobs,
            'departments' => $departments,
        ]);
    }

    /**
     * Admin list with filters and stats.
     */
    public function adminIndex(Request $request): JsonResponse
    {
        $query = JobPosting::query();

        if ($request->filled('department') && $request->department !== 'all') {
            $query->where('department', $request->department);
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        if ($request->filled('search')) {
            $search = '%'.$request->search.'%';
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', $search)
                    ->orWhere('department', 'LIKE', $search)
                    ->orWhere('location', 'LIKE', $search)
                    ->orWhere('description', 'LIKE', $search);
            });
        }

        $jobs = $query->orderBy('sort_order')->latest('id')->get();

        $counts = [
            'total' => JobPosting::count(),
            'active' => JobPosting::where('is_active', true)->count(),
            'inactive' => JobPosting::where('is_active', false)->count(),
        ];

        $departments = JobPosting::distinct()->pluck('department')->filter()->values();

        return response()->json([
            'success' => true,
            'data' => $jobs,
            'counts' => $counts,
            'departments' => $departments,
        ]);
    }

    /**
     * Store a newly created job posting.
     */
    public function store(StoreJobPostingRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $baseSlug = Str::slug($validated['title']);
        $slug = $baseSlug;
        $counter = 1;
        while (JobPosting::where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }
        $validated['slug'] = $slug;

        $job = JobPosting::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Job posting created successfully!',
            'job' => $job,
        ], 201);
    }

    /**
     * Update an existing job posting.
     */
    public function update(UpdateJobPostingRequest $request, JobPosting $job): JsonResponse
    {
        $validated = $request->validated();
        $job->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Job posting updated successfully!',
            'job' => $job->fresh(),
        ]);
    }

    /**
     * Toggle active/inactive status.
     */
    public function toggleActive(JobPosting $job): JsonResponse
    {
        $job->update([
            'is_active' => ! $job->is_active,
        ]);

        $statusStr = $job->is_active ? 'activated' : 'deactivated';

        return response()->json([
            'success' => true,
            'message' => "Job posting {$statusStr} successfully!",
            'job' => $job,
        ]);
    }

    /**
     * Delete a job posting.
     */
    public function destroy(JobPosting $job): JsonResponse
    {
        $job->delete();

        return response()->json([
            'success' => true,
            'message' => 'Job posting deleted successfully!',
        ]);
    }
}
