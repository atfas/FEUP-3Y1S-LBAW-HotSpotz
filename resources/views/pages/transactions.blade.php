@extends('layouts.app')

@section('content')
<div class="container my-4">
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

    @include('partials.page_header', [
        'breadcrumbs' => [
            ['title' => 'Profile', 'url' => route('profile.show')],
            ['title' => 'Payments', 'url' => null],
        ]
    ])

    <div class="card shadow-sm mb-4">
        <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-3">
            <h1 class="h3 mb-0" style="color: var(--color-dark-purple);">Payments & Transactions</h1>
            <div class="d-flex gap-3 align-items-center">
                <div class="text-center">
                    <small class="text-muted">Total Transactions</small>
                    <h5>{{ $totalTransactions ?? 0 }}</h5>
                </div>
                <div class="text-center">
                    <small class="text-muted">Pending</small>
                    <h5>{{ $pendingTransactions ?? 0 }}</h5>
                </div>
            </div>
        </div>
    </div>

    <ul class="nav nav-tabs mb-0" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="payments-tab-btn" data-bs-toggle="tab" data-bs-target="#payments-tab" type="button" role="tab" aria-controls="payments-tab" aria-selected="true">
                Payments <span class="badge bg-light text-dark ms-2">{{ $totalPayments ?? 0 }}</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="refunds-tab-btn" data-bs-toggle="tab" data-bs-target="#refunds-tab" type="button" role="tab" aria-controls="refunds-tab" aria-selected="false">
                Refunds <span class="badge bg-light text-dark ms-2">{{ $totalRefunds ?? 0 }}</span>
            </button>
        </li>
    </ul>

    <div class="tab-content">
        <div class="tab-pane fade show active" id="payments-tab" role="tabpanel" aria-labelledby="payments-tab-btn">
            <div class="card shadow-sm rounded-top-0">
                <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <h5 class="mb-0" style="color: var(--color-dark-purple);">Your Transactions</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 text-center">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Listing</th>
                                    <th>Check-in</th>
                                    <th>Check-out</th>
                                    <th>Type</th>
                                    <th>Method</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($payments as $t)
                                <tr>
                                    <td>{{ $t->paid_at ? \Carbon\Carbon::parse($t->paid_at)->format('Y-m-d H:i') : '—' }}</td>
                                    <td>
                                        @if($t->booking && $t->booking->availability && $t->booking->availability->listing)
                                            {{ $t->booking->availability->listing->title }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td>{{ $t->booking?->check_in_date ?? '—' }}</td>
                                    <td>{{ $t->booking?->check_out_date ?? '—' }}</td>
                                    <td>{{ $t->type }}</td>
                                    <td>{{ $t->transaction_method }}</td>
                                    <td>${{ number_format($t->amount, 2) }}</td>
                                    <td>
                                        <span class="badge 
                                            @if($t->status == 'Completed') bg-success
                                            @elseif($t->status == 'Pending') bg-warning text-dark
                                            @elseif($t->status == 'Failed') bg-danger
                                            @elseif($t->status == 'Refunded') bg-info text-dark
                                            @else bg-secondary
                                            @endif">
                                            {{ $t->status }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($t->status === 'Pending' && $t->booking && $t->booking->status === 'PendingPayment')
                                            <a href="{{ route('transactions.pay', $t->id) }}" class="action-btn text-success" title="Pay now">
                                                <i class="fas fa-credit-card"></i>
                                            </a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-4">
                                        <i class="fas fa-credit-card fa-2x mb-2"></i>
                                        <p>No payments found</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                @if($payments->hasPages())
                <div class="card-footer bg-white">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div class="text-muted small">
                            Showing {{ $payments->firstItem() }} to {{ $payments->lastItem() }} of {{ $payments->total() }} entries
                        </div>
                        <nav aria-label="Page navigation">
                            <ul class="pagination mb-0">
                                @if ($payments->onFirstPage())
                                    <li class="page-item disabled">
                                        <span class="page-link"><i class="fas fa-chevron-left"></i></span>
                                    </li>
                                @else
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $payments->previousPageUrl() }}" rel="prev">
                                            <i class="fas fa-chevron-left"></i>
                                        </a>
                                    </li>
                                @endif

                                @foreach ($payments->getUrlRange(1, $payments->lastPage()) as $page => $url)
                                    @if ($page == $payments->currentPage())
                                        <li class="page-item active"><span class="page-link">{{ $page }}</span></li>
                                    @else
                                        <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                                    @endif
                                @endforeach

                                @if ($payments->hasMorePages())
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $payments->nextPageUrl() }}" rel="next">
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

        <div class="tab-pane fade" id="refunds-tab" role="tabpanel" aria-labelledby="refunds-tab-btn">
            <div class="card shadow-sm rounded-top-0">
                <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <h5 class="mb-0" style="color: var(--color-dark-purple);">Refund Transactions</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 text-center">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Listing</th>
                                    <th>Check-in</th>
                                    <th>Check-out</th>
                                    <th>Method</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Original Txn</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($refunds as $t)
                                <tr>
                                    <td>{{ $t->paid_at ? \Carbon\Carbon::parse($t->paid_at)->format('Y-m-d H:i') : '—' }}</td>
                                    <td>{{ $t->booking?->availability?->listing?->title ?? '—' }}</td>
                                    <td>{{ $t->booking?->check_in_date ?? '—' }}</td>
                                    <td>{{ $t->booking?->check_out_date ?? '—' }}</td>
                                    <td>{{ $t->transaction_method }}</td>
                                    <td>${{ number_format($t->amount, 2) }}</td>
                                    <td>
                                        <span class="badge bg-info text-dark">{{ $t->status }}</span>
                                    </td>
                                    <td>
                                        @if($t->refund)
                                            <a href="#" class="text-muted">#{{ $t->refund->id }}</a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">
                                        <i class="fas fa-undo fa-2x mb-2"></i>
                                        <p>No refunds found</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                @if($refunds->hasPages())
                <div class="card-footer bg-white">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div class="text-muted small">
                            Showing {{ $refunds->firstItem() }} to {{ $refunds->lastItem() }} of {{ $refunds->total() }} entries
                        </div>
                        <nav aria-label="Page navigation">
                            <ul class="pagination mb-0">
                                @if ($refunds->onFirstPage())
                                    <li class="page-item disabled">
                                        <span class="page-link"><i class="fas fa-chevron-left"></i></span>
                                    </li>
                                @else
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $refunds->previousPageUrl() }}" rel="prev">
                                            <i class="fas fa-chevron-left"></i>
                                        </a>
                                    </li>
                                @endif

                                @foreach ($refunds->getUrlRange(1, $refunds->lastPage()) as $page => $url)
                                    @if ($page == $refunds->currentPage())
                                        <li class="page-item active"><span class="page-link">{{ $page }}</span></li>
                                    @else
                                        <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                                    @endif
                                @endforeach

                                @if ($refunds->hasMorePages())
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $refunds->nextPageUrl() }}" rel="next">
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

@push('styles')
<style>
    .action-btn { 
        border: none; 
        background: transparent; 
        padding: 0.25rem 0.5rem; 
        cursor: pointer; 
        transition: all 0.2s; 
        font-size: 1.1rem; 
    }
    .action-btn:hover { 
        background-color: #f8f9fa; 
        border-radius: 4px; 
        transform: scale(1.05); 
    }
    .nav-tabs { 
        border-bottom: none; 
    }
    .nav-tabs .nav-link {
        color: var(--color-dark-purple);
        border: 1px solid #dee2e6;
        border-bottom: none;
        background-color: #f8f9fa;
        border-top-left-radius: 0.375rem;
        border-top-right-radius: 0.375rem;
        border-bottom-left-radius: 0;
        border-bottom-right-radius: 0;
    }
    .nav-tabs .nav-link.active {
        background-color: #fff;
        color: var(--color-dark-purple);
        border-color: #dee2e6 #dee2e6 white #dee2e6;
        font-weight: 600;
    }
</style>
@endpush

@endsection