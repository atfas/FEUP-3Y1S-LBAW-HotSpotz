<?php

namespace App\Http\Controllers;

use App\Models\User;
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

        // Build the query
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

        // // Apply search filter if search query exists
        // if ($search) {
        //     $query->where(function($q) use ($search) {
        //         $q->where('full_name', 'like', "%{$search}%")
        //         ->orWhere('email', 'like', "%{$search}%");
        //     });
        // }

        // Order by registration date and paginate
        $users = $query->orderBy('registration_date', 'desc')
            ->paginate($perPage)
            ->withQueryString(); // Preserve query parameters across pages

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

        // Get total counts for dashboard stats
        $totalUsers = User::where('is_active', true)->count();
        $totalAdmins = User::where('is_active', true)
            ->where('is_admin', true)
            ->count();

        return view('pages.admin', compact('users', 'totalUsers', 'totalAdmins'));
    }

    public function searchUsers(Request $request): JsonResponse
    {
        // Gate::authorize('manageUsers');
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
        
        // Gate::authorize('toggleUserStatus', $targetUser);
        $this->authorize('toggleUserStatus', $targetUser);
        
        $targetUser->is_blocked = !$targetUser->is_blocked;
        $targetUser->save();

        return redirect()->route('admin.index')
            ->with('success', $targetUser->is_active 
                ? 'User account enabled successfully.' 
                : 'User account disabled successfully.');
    }

    public function toggleAdminStatus(Request $request, $id)
    {
        $targetUser = User::findOrFail($id);
        
        // Gate::authorize('changeAdminStatus', $targetUser);
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
        
        // Gate::authorize('deleteUser', $targetUser);
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

}