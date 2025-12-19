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

        <div class="d-flex justify-content-center mt-4">
            {{ $notifications->links() }}
        </div>
    @endif
</div>
@endsection
