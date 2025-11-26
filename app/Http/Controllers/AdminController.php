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
        Gate::authorize('viewAdminPanel');

        // Get all users with their statistics
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

        return view('pages.admin', compact('users'));
    }

    /**
     * Search users by name or email.
     */
    public function searchUsers(Request $request): JsonResponse
    {
        Gate::authorize('manageUsers');

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
        
        Gate::authorize('toggleUserStatus', $targetUser);

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
        
        Gate::authorize('changeAdminStatus', $targetUser);

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
        
        Gate::authorize('deleteUser', $targetUser);

        $targetUser->is_active = false;
        $targetUser->save();

        return redirect()->route('admin.index')
            ->with('success', 'User deleted successfully.');
    }

    /**
     * Get admin dashboard statistics.
     */
    public function getStatistics(): JsonResponse
    {
        Gate::authorize('viewAdminPanel');

        $stats = [
            'total_users' => User::where('is_active', true)->count(),
            'total_admins' => User::where('is_admin', true)->where('is_active', true)->count(),
            'blocked_users' => User::where('is_blocked', true)->where('is_active', true)->count(),
            'pending_applications' => User::where('application_status', 'Pending')->where('is_active', true)->count(),
        ];

        return response()->json($stats);
    }
}