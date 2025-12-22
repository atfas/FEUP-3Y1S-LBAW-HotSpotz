<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{

    public function index(): View
    {
        // Check if user is authorized to view admin panel
        $this->authorize('viewAdminPanel');
        
        $perPage = 10;
        
        // Get search query if exists
        $search = request('search');

        // Build the query for users
        $query = User::select(
            'id',
            'full_name',
            'email',
            'is_admin',
            'is_active',
            'is_blocked',
            'registration_date',
            'application_status'
        )
        ->where('is_active', true);

        // Order by registration date and paginate
        $users = $query->orderBy('registration_date', 'desc')
            ->paginate($perPage)
            ->withQueryString();

        // Transform the paginated collection
        $users->getCollection()->transform(function ($user) {
            return [
                'id' => $user->id,
                'username' => $user->full_name,
                'email' => $user->email,
                'role' => $user->is_admin ? 'Admin' : ($user->application_status === 'Approved' ? 'Host' : 'User'),
                'is_admin' => $user->is_admin,
                'active' => $user->is_active,
                'blocked' => $user->is_blocked,
                'joined_date' => $user->registration_date->format('F d, Y'),
                'application_status' => $user->application_status,
            ];
        });

        // Get reported reviews
        $reportedReviews = Review::with([
            'guest',
            'host',
            'reporter',
            'reviewer',
            'booking.availability.listing'
        ])
        ->reported()
        ->orderByRaw("
            CASE 
                WHEN report_decision = 'pending' OR report_decision IS NULL 
                THEN 0 ELSE 1 
            END
        ")
        ->orderBy('reported_at', 'desc')
        ->paginate($perPage, ['*'], 'reports_page');


        // Get total counts for dashboard stats
        $totalUsers = User::where('is_active', true)->count();
        $totalAdmins = User::where('is_active', true)
            ->where('is_admin', true)
            ->count();
        $pendingReportsCount = Review::pendingReports()->count();

        return view('pages.admin', compact('users', 'reportedReviews', 'totalUsers', 'totalAdmins', 'pendingReportsCount'));
    }

    public function searchUsers(Request $request): JsonResponse
    {
        $this->authorize('manageUsers');

        $search = $request->input('search', '');

        $users = User::select(
            'id',
            'full_name',
            'email',
            'is_admin',
            'is_active',
            'is_blocked',
            'registration_date',
            'application_status'
        )
        ->where('is_active', true)
        ->where(function ($query) use ($search) {
            $query->where('full_name', 'ILIKE', "%{$search}%")
                  ->orWhere('email', 'ILIKE', "%{$search}%");
        })
        ->orderBy('registration_date', 'desc')
        ->get()
        ->map(function ($user) {
            return [
                'id' => $user->id,
                'username' => $user->full_name,
                'email' => $user->email,
                'role' => $user->is_admin ? 'Admin' : 'User',
                'is_admin' => $user->is_admin,
                'active' => $user->is_active,
                'blocked' => $user->is_blocked,
                'joined_date' => $user->registration_date->format('F d, Y'),
                'application_status' => $user->application_status,
            ];
        });

        return response()->json(['users' => $users]);
    }

    public function toggleUserStatus(Request $request, $id)
    {
        $targetUser = User::findOrFail($id);
        
        $this->authorize('toggleUserStatus', $targetUser);
        
        $targetUser->is_blocked = !$targetUser->is_blocked;
        $targetUser->save();

        return redirect()->route('admin.index')
            ->with('success', $targetUser->is_blocked 
                ? 'User account disabled successfully.' 
                : 'User account enabled successfully.');
    }

    public function toggleAdminStatus(Request $request, $id)
    {
        $targetUser = User::findOrFail($id);
        
        $this->authorize('changeAdminStatus', $targetUser);
        
        $targetUser->is_admin = !$targetUser->is_admin;
        $targetUser->save();

        return redirect()->route('admin.index')
            ->with('success', $targetUser->is_admin 
                ? 'User promoted to admin successfully.' 
                : 'User unpromoted from admin successfully.');
    }

    public function deleteUser($id)
    {
        $targetUser = User::findOrFail($id);
        
        $this->authorize('deleteUser', $targetUser);
        
        $targetUser->is_active = false;
        $targetUser->save();

        return redirect()->route('admin.index')
            ->with('success', 'User deleted successfully.');
    }

    /**
     * Show a user's host application.
     */
    public function showApplication($id): View|RedirectResponse
    {
        Gate::authorize('viewAdminPanel');
        
        $user = User::findOrFail($id);
        
        // Verify user has an application
        if ($user->application_status === null) {
            return redirect()->route('admin.index')
                ->with('error', 'This user has not submitted a host application.');
        }
        
        return view('pages.admin_application_review', compact('user'));
    }

    /**
     * Approve a host application.
     */
    public function approveApplication(Request $request, $id): RedirectResponse
    {
        Gate::authorize('viewAdminPanel');
        
        $user = User::findOrFail($id);
        
        if ($user->application_status !== 'Pending') {
            return redirect()->route('admin.index')
                ->with('error', 'This application has already been processed.');
        }
        
        $user->application_status = 'Approved';
        $user->verified_at = now();
        $user->verifier_id = Auth::id();
        $user->save();
        
        return redirect()->route('admin.index')
            ->with('success', "Host application for {$user->full_name} has been approved.");
    }

    /**
     * Reject a host application.
     */
    public function rejectApplication(Request $request, $id): RedirectResponse
    {
        Gate::authorize('viewAdminPanel');
        
        $user = User::findOrFail($id);
        
        if ($user->application_status !== 'Pending') {
            return redirect()->route('admin.index')
                ->with('error', 'This application has already been processed.');
        }
        
        // Set status to Rejected
        $user->application_status = 'Rejected';
        $user->save();
        
        return redirect()->route('admin.index')
            ->with('success', "Host application for {$user->full_name} has been rejected.");
    }



    /**
     * View detailed report information for a review.
     */
    public function viewReport($id): View
    {
        Gate::authorize('viewAdminPanel');
        
        $review = Review::with(['guest', 'host', 'booking.availability.listing', 'reporter', 'reviewer'])
            ->findOrFail($id);

        if (!$review->reported) {
            abort(404, 'Report not found.');
        }

        return view('pages.admin_viewReport', compact('review'));  // Changed from 'pages.admin.viewReport'
    }

    /**
     * Dismiss a report (keep the review).
     */
    public function dismissReport($id): RedirectResponse
    {
        Gate::authorize('viewAdminPanel');
        
        $review = Review::findOrFail($id);

        if (!$review->reported) {
            return back()->with('error', 'This review has not been reported.');
        }

        if ($review->report_decision && $review->report_decision !== 'pending') {
            return back()->with('error', 'This report has already been processed.');
        }

        $review->update([
            'report_decision' => 'dismissed',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        return back()->with('success', 'Report dismissed successfully.');
    }

    /**
     * Remove a reported review.
     */
    public function removeReportedReview($id): RedirectResponse
    {
        Gate::authorize('viewAdminPanel');
        
        $review = Review::findOrFail($id);

        if (!$review->reported) {
            return back()->with('error', 'This review has not been reported.');
        }

        if ($review->report_decision && $review->report_decision !== 'pending') {
            return back()->with('error', 'This report has already been processed.');
        }

        // Update report status before deletion
        $review->update([
            'report_decision' => 'removed',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        // Delete the review
        $review->delete();

        return redirect()->route('admin.index')
            ->with('success', 'Review has been removed successfully.');
    }
}