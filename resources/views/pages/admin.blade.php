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
                <div class="text-center">
                    <small class="text-muted">Pending Reports</small>
                    <h5 id="pendingReports" class="text-danger">{{ $pendingReportsCount ?? 0 }}</h5>
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
        <li class="nav-item" role="presentation">
            <button class="nav-link position-relative" id="reports-tab" data-bs-toggle="tab" data-bs-target="#reports" type="button" role="tab">
                Reports
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
                                <tr>
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
                                    <td colspan="7" class="text-center text-muted py-4">
                                        <i class="fas fa-users fa-2x mb-2"></i>
                                        <p>No users found</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                
                @if($users->hasPages())
                <div class="card-footer bg-white">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div class="text-muted small">
                            Showing {{ $users->firstItem() }} to {{ $users->lastItem() }} of {{ $users->total() }} entries
                        </div>
                        
                        <nav aria-label="Page navigation">
                            <ul class="pagination mb-0">
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

                                @foreach ($users->getUrlRange(1, $users->lastPage()) as $page => $url)
                                    @if ($page == $users->currentPage())
                                        <li class="page-item active"><span class="page-link">{{ $page }}</span></li>
                                    @else
                                        <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                                    @endif
                                @endforeach

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

        <!-- Reports Tab -->
        <div class="tab-pane fade" id="reports" role="tabpanel">
            <div class="card shadow-sm rounded-top-0">
                <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <h5 class="mb-0" style="color: var(--color-dark-purple);">Review Reports</h5>
                    <div class="d-flex gap-2">

                        <div class="input-group" style="max-width: 300px;">
                            <input type="text" class="form-control form-control-sm" id="reportSearch" placeholder="Search reports...">
                            <span class="input-group-text">
                                <i class="fas fa-search"></i>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="reportsTable">
                            <thead>
                                <tr>
                                    <th>Review Details</th>
                                    <th>Report Info</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($reportedReviews ?? [] as $review)
                                <tr data-report-status="{{ $review->report_decision ?? 'pending' }}">
                                    <td>
                                        <div class="d-flex flex-column gap-1">
                                            <span>
                                                @if($review->guest)
                                                    {{ $review->guest->full_name }}
                                                @else
                                                    Unknown (ID: {{ $review->guest ?? 'NULL' }})
                                                @endif
                                            </span>
                                            <div class="review-rating">
                                                @for($i = 1; $i <= 5; $i++)
                                                    @if($i <= $review->rating)
                                                        <i class="fas fa-star text-warning"></i>
                                                    @else
                                                        <i class="far fa-star text-muted"></i>
                                                    @endif
                                                @endfor
                                            </div>
                                            <p class="mb-0 text-muted small">{{ Str::limit($review->comment, 100) }}</p>
                   
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column gap-1 small">
                                            @if($review->booking?->availability?->listing)
                                                <a href="{{ route('listing.show', $review->booking->availability->listing->id) }}"
                                                target="_blank"
                                                class="text-decoration-none">
                                                    {{ $review->booking->availability->listing->title }}
                                                    <i class="fas fa-external-link-alt ms-1"></i>
                                                </a>
                                            @else
                                                <span class="text-muted">Unavailable</span>
                                            @endif


                                            <span><strong>Reason:</strong> {{ $review->report_reason }}</span>
                                            <span><strong>Reported by:</strong> 
                                                @if($review->reporter)
                                                    {{ $review->reporter->full_name }}
                                                @else
                                                    Unknown (ID: {{ $review->reported_by ?? 'NULL' }})
                                                @endif
                                            </span>
                                            <span><strong>Reported:</strong> {{ $review->reported_at?->diffForHumans() }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        @php
                                            $status = $review->report_decision ?? 'pending';
                                            $badgeClass = match($status) {
                                                'pending' => 'bg-warning',
                                                'dismissed' => 'bg-secondary',
                                                'removed' => 'bg-danger',
                                                default => 'bg-secondary'
                                            };
                                        @endphp
                                        <span class="badge {{ $badgeClass }}">
                                            {{ ucfirst($status) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if(($review->report_decision ?? 'pending') === 'pending')
                                            <div class="d-flex gap-2">
                                                <form action="{{ route('admin.reports.dismiss', $review->id) }}" 
                                                      method="POST" 
                                                      style="display: inline;"
                                                      onsubmit="return confirm('Are you sure you want to dismiss this report?')">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-secondary" title="Dismiss report">
                                                        <i class="fas fa-times me-1"></i>Dismiss
                                                    </button>
                                                </form>
                                                <form action="{{ route('admin.reports.remove', $review->id) }}" 
                                                      method="POST" 
                                                      style="display: inline;"
                                                      onsubmit="return confirm('Are you sure you want to remove this review? This action cannot be undone.')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" title="Remove review">
                                                        <i class="fas fa-trash me-1"></i>Remove
                                                    </button>
                                                </form>
                                            </div>
                                        @else
                                            <span class="text-muted small">{{ ucfirst($review->report_decision) }}</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">
                                        <i class="fas fa-flag fa-2x mb-2"></i>
                                        <p>No reported reviews found</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                @if(isset($reportedReviews) && $reportedReviews->hasPages())
                <div class="card-footer bg-white">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div class="text-muted small">
                            Showing {{ $reportedReviews->firstItem() }} to {{ $reportedReviews->lastItem() }} of {{ $reportedReviews->total() }} entries
                        </div>
                        
                        <nav aria-label="Page navigation">
                            <ul class="pagination mb-0">
                                @if ($reportedReviews->onFirstPage())
                                    <li class="page-item disabled">
                                        <span class="page-link"><i class="fas fa-chevron-left"></i></span>
                                    </li>
                                @else
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $reportedReviews->previousPageUrl() }}" rel="prev">
                                            <i class="fas fa-chevron-left"></i>
                                        </a>
                                    </li>
                                @endif

                                @foreach ($reportedReviews->getUrlRange(1, $reportedReviews->lastPage()) as $page => $url)
                                    @if ($page == $reportedReviews->currentPage())
                                        <li class="page-item active"><span class="page-link">{{ $page }}</span></li>
                                    @else
                                        <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                                    @endif
                                @endforeach

                                @if ($reportedReviews->hasMorePages())
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $reportedReviews->nextPageUrl() }}" rel="next">
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
// User search functionality
document.getElementById('userSearch').addEventListener('keyup', function () {
    const value = this.value.toLowerCase();
    document.querySelectorAll('#usersTable tbody tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(value) ? '' : 'none';
    });
});

// Report search functionality
document.getElementById('reportSearch').addEventListener('keyup', function () {
    const value = this.value.toLowerCase();
    document.querySelectorAll('#reportsTable tbody tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(value) ? '' : 'none';
    });
});


</script>

@endsection