@extends('layouts.app')
@section('title', 'Admin Panel')

@section('content')
<link rel="stylesheet" href="{{ asset('css/admin.panel.css') }}">

<div class="container my-4">
    
    <!-- Flash Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Breadcrumbs -->
    @include('partials.page_header', [
        'breadcrumbs' => [
            ['title' => 'Admin panel', 'url' => null],
        ]
    ])


    <!-- Admin Panel Header -->
    <div class="card shadow-sm mb-4">
        <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-3">
            <h1 class="h3 mb-0" style="color: var(--color-dark-purple);">Admin Dashboard</h1>
                            <div class="d-flex gap-3 align-items-center">
                <div class="text-center">
                    <small class="text-muted">Total Users</small>
                    <h5 id="totalUsers">{{ $totalUsers }}</h5>
                </div>
                <div class="text-center">
                    <small class="text-muted">Admins</small>
                    <h5 id="totalAdmins">{{ $totalAdmins }}</h5>
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
                        <table class="table table-hover mb-0 text-center" id="usersTable">
                            <thead>
                                <tr>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Blocked</th>
                                    <th>Joined Date</th>
                                    <th>Application</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users as $user)
                                <tr data-user-id="{{ $user['id'] }}">
                                    <td>{{ $user['username'] }}</td>
                                    <td>{{ $user['email'] }}</td>
                                    <td>
                                        <span class="badge {{ $user['is_admin'] ? 'wine-badge' : ($user['application_status'] === 'Approved' ? 'bg-warning' : 'bg-secondary') }}">
                                            {{ $user['role'] }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge {{ $user['blocked'] ? 'bg-danger' : 'bg-success' }}">
                                            {{ $user['blocked'] ? 'Yes' : 'No' }}
                                        </span>
                                    </td>
                                    <td>{{ $user['joined_date'] }}</td>
                                    <td>
                                        @if($user['application_status'] === 'Pending')
                                            <a href="{{ route('admin.applications.show', $user['id']) }}" class="btn btn-sm btn-warning">
                                                <i class="fa-solid fa-eye me-1"></i>Review
                                            </a>
                                        @elseif($user['application_status'] === 'Rejected')
                                            <span class="badge bg-danger">Rejected</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($user['id'] !== Auth::id())
                                            <!-- Toggle Status Form -->
                                            <form action="{{ route('admin.users.toggle-status', $user['id']) }}" 
                                                  method="POST" 
                                                  style="display: inline;"
                                                  onsubmit="return confirm('Are you sure you want to change this user\'s status?')">
                                                @csrf
                                                <button type="submit" class="action-btn {{ $user['blocked'] ? 'text-danger' : 'text-success' }}" 
                                                        title="{{ $user['blocked'] ? 'Disable Account' : 'Enable Account' }}">
                                                    <i class="fas fa-user-{{ $user['blocked'] ? 'slash' : 'check' }}"></i>
                                                </button>
                                            </form>

                                            <!-- Toggle Admin Form -->
                                            <form action="{{ route('admin.users.toggle-admin', $user['id']) }}" 
                                                  method="POST" 
                                                  style="display: inline;"
                                                  onsubmit="return confirm('Are you sure you want to change this user\'s admin status?')">
                                                @csrf
                                                <button type="submit" class="action-btn {{ $user['is_admin'] ? 'text-warning' : 'text-success' }}" 
                                                        title="{{ $user['is_admin'] ? 'Unpromote from Admin' : 'Promote to Admin' }}">
                                                    <i class="fas fa-user-{{ $user['is_admin'] ? 'times' : 'shield' }}"></i>
                                                </button>
                                            </form>

                                            <!-- Delete User Form -->
                                            <form action="{{ route('admin.users.destroy', $user['id']) }}" 
                                                  method="POST" 
                                                  style="display: inline;"
                                                  onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="action-btn text-danger" title="Delete user">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-muted"><em>Me</em></span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">
                                        <i class="fas fa-users fa-2x mb-2"></i>
                                        <p>No users found</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Pagination Footer -->
                @if($users->hasPages())
                <div class="card-footer bg-white">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div class="text-muted small">
                            Showing {{ $users->firstItem() }} to {{ $users->lastItem() }} of {{ $users->total() }} entries
                        </div>
                        
                        <nav aria-label="Page navigation">
                            <ul class="pagination mb-0">
                                {{-- Previous Button --}}
                                @if ($users->onFirstPage())
                                    <li class="page-item disabled">
                                        <span class="page-link"><i class="fas fa-chevron-left"></i></span>
                                    </li>
                                @else
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $users->previousPageUrl() }}" rel="prev">
                                            <i class="fas fa-chevron-left"></i>
                                        </a>
                                    </li>
                                @endif

                                {{-- Pagination Elements --}}
                                @foreach ($users->getUrlRange(1, $users->lastPage()) as $page => $url)
                                    @if ($page == $users->currentPage())
                                        <li class="page-item active"><span class="page-link">{{ $page }}</span></li>
                                    @else
                                        <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                                    @endif
                                @endforeach

                                {{-- Next Button --}}
                                @if ($users->hasMorePages())
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $users->nextPageUrl() }}" rel="next">
                                            <i class="fas fa-chevron-right"></i>
                                        </a>
                                    </li>
                                @else
                                    <li class="page-item disabled">
                                        <span class="page-link"><i class="fas fa-chevron-right"></i></span>
                                    </li>
                                @endif
                            </ul>
                        </nav>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('userSearch').addEventListener('keyup', function () {
    const value = this.value.toLowerCase();

    document.querySelectorAll('#usersTable tbody tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(value)
            ? ''
            : 'none';
    });
});
</script>

@endsection
