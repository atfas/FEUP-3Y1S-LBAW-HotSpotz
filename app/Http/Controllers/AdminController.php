<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    /**
     * Display the admin dashboard.
     */
    public function index(): View
{
        // Check if user is authorized to view admin panel
        Gate::authorize('viewAdminPanel', User::class);

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
                'role' => $user->is_admin ? 'Admin' : 'User',
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

    /**
     * Search users by name or email.
     */
    public function searchUsers(Request $request): JsonResponse
    {
        Gate::authorize('manageUsers', User::class);

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
        
        Gate::authorize('toggleUserStatus', [User::class, $targetUser]);

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
        
        Gate::authorize('changeAdminStatus', [User::class, $targetUser]);

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
        
        Gate::authorize('deleteUser', [User::class, $targetUser]);

        $targetUser->is_active = false;
        $targetUser->save();

        return redirect()->route('admin.index')
            ->with('success', 'User deleted successfully.');
    }

}