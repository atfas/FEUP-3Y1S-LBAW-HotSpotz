@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/admin.panel.css') }}">

<div class="container my-4">
    <!-- Admin Panel Header -->
    <div class="card shadow-sm mb-4">
        <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-3">
            <h1 class="h3 mb-0" style="color: var(--color-dark-purple);">Admin Dashboard</h1>
            <div class="d-flex gap-3">
                <div class="text-center">
                    <small class="text-muted">Total Users</small>
                    <h5 id="totalUsers">{{ $users->count() }}</h5>
                </div>
                <div class="text-center">
                    <small class="text-muted">Admins</small>
                    <h5 id="totalAdmins">{{ $users->where('is_admin', true)->count() }}</h5>
                </div>
            </div>
        </div>
    </div>

    <!-- Admin Tabs -->
    <ul class="nav nav-tabs mb-0" id="adminTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="users-tab" data-bs-toggle="tab" data-bs-target="#users" type="button" role="tab">
                Users
            </button>
        </li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content" id="adminTabContent">
        <!-- Users Tab -->
        <div class="tab-pane fade show active" id="users" role="tabpanel">
            <div class="card shadow-sm rounded-top-0">
                <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <h5 class="mb-0" style="color: var(--color-dark-purple);">Manage Users</h5>
                    <div class="input-group" style="max-width: 300px;">
                        <input type="text" class="form-control" id="userSearch" placeholder="Search users...">
                        <span class="input-group-text">
                            <i class="fas fa-search"></i>
                        </span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="usersTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Active</th>
                                    <th>Joined Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($users as $user)
                                <tr data-user-id="{{ $user['id'] }}">
                                    <td>{{ $user['id'] }}</td>
                                    <td>{{ $user['username'] }}</td>
                                    <td>{{ $user['email'] }}</td>
                                    <td>
                                        <span class="badge {{ $user['is_admin'] ? 'wine-badge' : 'bg-secondary' }}">
                                            {{ $user['role'] }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge {{ $user['active'] ? 'bg-success' : 'bg-secondary' }}">
                                            {{ $user['active'] ? 'Yes' : 'No' }}
                                        </span>
                                    </td>
                                    <td>{{ $user['joined_date'] }}</td>
                                    <td>
                                        @if($user['id'] !== Auth::id())
                                            @if($user['active'])
                                                <button class="action-btn text-danger toggle-status-btn" 
                                                        title="Disable Account" 
                                                        data-id="{{ $user['id'] }}"
                                                        data-action="disable">
                                                    <i class="fas fa-user-slash"></i>
                                                </button>
                                            @else
                                                <button class="action-btn text-success toggle-status-btn" 
                                                        title="Enable Account" 
                                                        data-id="{{ $user['id'] }}"
                                                        data-action="enable">
                                                    <i class="fas fa-user-check"></i>
                                                </button>
                                            @endif

                                            @if($user['is_admin'])
                                                <button class="action-btn text-warning toggle-admin-btn" 
                                                        title="Unpromote from Admin" 
                                                        data-id="{{ $user['id'] }}">
                                                    <i class="fas fa-user-times"></i>
                                                </button>
                                            @else
                                                <button class="action-btn text-success toggle-admin-btn" 
                                                        title="Promote to Admin" 
                                                        data-id="{{ $user['id'] }}">
                                                    <i class="fas fa-user-shield"></i>
                                                </button>
                                            @endif

                                            <button class="action-btn text-danger delete-user-btn" 
                                                    title="Delete user" 
                                                    data-id="{{ $user['id'] }}">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @else
                                            <span class="text-muted"><em>You</em></span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Admin panel script loaded');
    
    const userSearch = document.getElementById('userSearch');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    
    console.log('CSRF Token:', csrfToken);
    console.log('User Search Element:', userSearch);
    
    if (!csrfToken) {
        console.error('CSRF token not found! Make sure you have <meta name="csrf-token"> in your layout');
        alert('CSRF token missing!');
        return;
    }

    // Search functionality
    let searchTimeout;
    if (userSearch) {
        userSearch.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                const searchValue = this.value;
                console.log('Searching for:', searchValue);
                
                fetch(`/admin/search-users?search=${encodeURIComponent(searchValue)}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    console.log('Search results:', data);
                    updateUsersTable(data.users);
                })
                .catch(error => console.error('Search Error:', error));
            }, 300);
        });
    }

    // Consolidated click handler for all buttons
    document.addEventListener('click', function(e) {
        console.log('Document clicked:', e.target);
        
        // Toggle user status
        if (e.target.closest('.toggle-status-btn')) {
            console.log('Toggle status button clicked');
            const btn = e.target.closest('.toggle-status-btn');
            const userId = btn.dataset.id;
            console.log('User ID:', userId);
            
            if (confirm('Are you sure you want to change this user\'s status?')) {
                fetch(`/admin/users/${userId}/toggle-status`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    console.log('Toggle status response:', data);
                    if (data.success) {
                        location.reload();
                    }
                })
                .catch(error => console.error('Toggle Status Error:', error));
            }
            return; // Prevent further processing
        }

        // Toggle admin status
        if (e.target.closest('.toggle-admin-btn')) {
            console.log('Toggle admin button clicked');
            alert("Admin button clicked!");
            
            const btn = e.target.closest('.toggle-admin-btn');
            const userId = btn.dataset.id;
            console.log('User ID:', userId);
            
            if (confirm('Are you sure you want to change this user\'s admin status?')) {
                fetch(`/admin/users/${userId}/toggle-admin`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    console.log('Toggle admin response:', data);
                    if (data.success) {
                        location.reload();
                    }
                })
                .catch(error => console.error('Toggle Admin Error:', error));
            }
            return; // Prevent further processing
        }

        // Delete user
        if (e.target.closest('.delete-user-btn')) {
            console.log('Delete button clicked');
            const btn = e.target.closest('.delete-user-btn');
            const userId = btn.dataset.id;
            console.log('User ID:', userId);
            
            if (confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
                fetch(`/admin/users/${userId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    console.log('Delete response:', data);
                    if (data.success) {
                        location.reload();
                    }
                })
                .catch(error => console.error('Delete Error:', error));
            }
            return; // Prevent further processing
        }
    });

    function updateUsersTable(users) {
        const tbody = document.querySelector('#usersTable tbody');
        tbody.innerHTML = '';
        
        users.forEach(user => {
            const row = createUserRow(user);
            tbody.appendChild(row);
        });
    }

    

    console.log('Admin panel script fully initialized');
});
</script>
@endsection