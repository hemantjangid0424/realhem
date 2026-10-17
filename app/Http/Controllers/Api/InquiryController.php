<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Inquiry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InquiryController extends Controller
{
    /**
     * Submit an inquiry, feedback, support request, or problem report (Public).
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => 'required|string|in:contact,request_info,feedback,report_problem,career',
            'name' => 'required|string|max:150',
            'email' => 'required|email|max:150',
            'phone' => 'nullable|string|max:30',
            'subject' => 'nullable|string|max:200',
            'message' => 'required|string|max:5000',
            'rating' => 'nullable|integer|min:1|max:5',
            'property_id' => 'nullable|integer|exists:properties,id',
            'metadata' => 'nullable|array',
        ]);

        $inquiry = Inquiry::create([
            'type' => $validated['type'],
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'subject' => $validated['subject'] ?? null,
            'message' => $validated['message'],
            'rating' => $validated['rating'] ?? null,
            'property_id' => $validated['property_id'] ?? null,
            'status' => 'pending',
            'metadata' => $validated['metadata'] ?? null,
        ]);

        $thankYouMessage = match ($validated['type']) {
            'feedback' => 'Thank you for your valuable feedback! We appreciate your support in improving RealHem.',
            'report_problem' => 'Thank you for reporting this issue. Our trust & safety moderation desk will review it shortly.',
            'request_info' => 'Thank you! Your information request has been received. A property specialist will contact you soon.',
            'career' => 'Thank you for applying to RealHem! Our recruitment team will review your application.',
            default => 'Thank you for contacting us! Our team will respond to your inquiry promptly.',
        };

        return response()->json([
            'success' => true,
            'message' => $thankYouMessage,
            'inquiry' => $inquiry,
        ], 201);
    }

    /**
     * Get paginated inquiries & reports (Admin only).
     */
    public function adminIndex(Request $request): JsonResponse
    {
        $query = Inquiry::query()->with('property:id,title,city,locality,expected_price');

        if ($request->filled('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = '%'.$request->search.'%';
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', $search)
                    ->orWhere('email', 'LIKE', $search)
                    ->orWhere('phone', 'LIKE', $search)
                    ->orWhere('subject', 'LIKE', $search)
                    ->orWhere('message', 'LIKE', $search);
            });
        }

        $inquiries = $query->latest()->paginate(15);

        $counts = [
            'total' => Inquiry::count(),
            'pending' => Inquiry::where('status', 'pending')->count(),
            'resolved' => Inquiry::where('status', 'resolved')->count(),
            'contact' => Inquiry::where('type', 'contact')->count(),
            'request_info' => Inquiry::where('type', 'request_info')->count(),
            'feedback' => Inquiry::where('type', 'feedback')->count(),
            'report_problem' => Inquiry::where('type', 'report_problem')->count(),
            'career' => Inquiry::where('type', 'career')->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $inquiries->items(),
            'pagination' => [
                'current_page' => $inquiries->currentPage(),
                'last_page' => $inquiries->lastPage(),
                'total' => $inquiries->total(),
                'per_page' => $inquiries->perPage(),
            ],
            'counts' => $counts,
        ]);
    }

    /**
     * Update inquiry/report status (Admin only).
     */
    public function updateStatus(Request $request, Inquiry $inquiry): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|string|in:pending,resolved,dismissed',
        ]);

        $inquiry->update([
            'status' => $validated['status'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Status updated to '.ucfirst($validated['status']).' successfully!',
            'inquiry' => $inquiry,
        ]);
    }

    /**
     * Delete an inquiry (Admin only).
     */
    public function destroy(Inquiry $inquiry): JsonResponse
    {
        $inquiry->delete();

        return response()->json([
            'success' => true,
            'message' => 'Inquiry record deleted successfully.',
        ]);
    }
}
