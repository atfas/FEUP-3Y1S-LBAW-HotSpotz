@extends('layouts.app')

@section('content')
<div class="container my-4">
    <div class="card shadow-sm mb-4">
        <div class="card-body d-flex justify-content-between align-items-center">
            <h2 class="mb-0"><i class="fa-solid fa-bell me-2"></i>Notifications</h2>
            @if($unreadCount > 0)
                <form action="{{ route('notifications.read-all') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-outline-primary btn-sm">
                        <i class="fa-solid fa-check-double me-1"></i>Mark all as read
                    </button>
                </form>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($notifications->isEmpty())
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fa-regular fa-bell-slash fa-3x text-muted mb-3"></i>
                <p class="text-muted mb-0">You have no notifications yet.</p>
            </div>
        </div>
    @else
        <div class="list-group" id="notifications-list">
            @foreach($notifications as $notification)
                @include('partials.notification', ['notification' => $notification])
            @endforeach
        </div>

        @if($notifications->hasPages())
        <div class="card shadow-sm mt-4">
            <div class="card-footer bg-white">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div class="text-muted small">
                        Showing {{ $notifications->firstItem() }} to {{ $notifications->lastItem() }} of {{ $notifications->total() }} entries
                    </div>
                    
                    <nav aria-label="Page navigation">
                        <ul class="pagination mb-0">
                            {{-- Previous Button --}}
                            @if ($notifications->onFirstPage())
                                <li class="page-item disabled">
                                    <span class="page-link"><i class="fas fa-chevron-left"></i></span>
                                </li>
                            @else
                                <li class="page-item">
                                    <a class="page-link" href="{{ $notifications->previousPageUrl() }}" rel="prev">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                </li>
                            @endif

                            {{-- Pagination Elements --}}
                            @foreach ($notifications->getUrlRange(1, $notifications->lastPage()) as $page => $url)
                                @if ($page == $notifications->currentPage())
                                    <li class="page-item active"><span class="page-link">{{ $page }}</span></li>
                                @else
                                    <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                                @endif
                            @endforeach

                            {{-- Next Button --}}
                            @if ($notifications->hasMorePages())
                                <li class="page-item">
                                    <a class="page-link" href="{{ $notifications->nextPageUrl() }}" rel="next">
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
        </div>
        @endif
    @endif
</div>
@endsection
