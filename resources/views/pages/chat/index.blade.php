@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <!-- Breadcrumbs -->
    @include('partials.page_header', [
        'breadcrumbs' => [
            ['title' => 'Messages', 'url' => null],
        ]
    ])
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-wine text-white">
                    <h4 class="mb-0"><i class="fa-solid fa-message"></i> Messages</h4>
                </div>
                <div class="card-body p-0">
                    @if($conversations->isEmpty())
                        <div class="text-center p-5">
                            <i class="fa-solid fa-inbox fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No conversations yet. Start a chat from a listing or booking!</p>
                        </div>
                    @else
                        <div class="list-group list-group-flush" id="conversations-list">
                            @foreach($conversations as $conv)
                                <a href="{{ route('chat.show', $conv['id']) }}" class="list-group-item list-group-item-action" data-conversation-id="{{ $conv['id'] }}" data-is-guest="{{ $conv['is_guest'] ? 'true' : 'false' }}">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <h5 class="mb-1">
                                                <span class="conversation-title">{{ $conv['title'] }}</span>
                                                @if($conv['unread_count'] > 0)
                                                    <span class="badge bg-primary rounded-pill unread-badge">{{ $conv['unread_count'] }}</span>
                                                @endif
                                            </h5>
                                            <p class="mb-1 text-muted">
                                                <small class="last-message">
                                                    @if($conv['last_message'])
                                                        {{ Str::limit($conv['last_message']->content, 60) }}
                                                    @else
                                                        No messages yet
                                                    @endif
                                                </small>
                                            </p>
                                            <small class="text-muted">
                                                {{ $conv['created_at']->diffForHumans() }}
                                            </small>
                                        </div>
                                        <div class="ms-3">
                                            @if($conv['other_user']->profile_picture_url)
                                                <img src="{{ $conv['other_user']->profile_picture_url }}" 
                                                     alt="{{ $conv['other_user']->full_name }}" 
                                                     class="rounded-circle" 
                                                     style="width: 50px; height: 50px; object-fit: cover;">
                                            @else
                                                <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center" 
                                                     style="width: 50px; height: 50px;">
                                                    <i class="fa-solid fa-user text-white"></i>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
@if(config('broadcasting.default') === 'pusher')
<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const userId = {{ Auth::id() }};
        
        const pusher = new Pusher('{{ config('broadcasting.connections.pusher.key') }}', {
            cluster: '{{ config('broadcasting.connections.pusher.options.cluster') }}',
            encrypted: true,
            authEndpoint: '/broadcasting/auth',
            auth: {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            }
        });
        
        const channel = pusher.subscribe('private-user.' + userId);
        
        channel.bind('message.sent', function(data) {
            const conversationId = data.conversation_id;
            const conversationLink = document.querySelector(`[data-conversation-id="${conversationId}"]`);
            
            if (conversationLink) {
                const isGuest = conversationLink.dataset.isGuest === 'true';
                const expectedAuthor = isGuest ? 'Host' : 'Guest';
                const isReceiver = data.message.author === expectedAuthor;
                
                const lastMessageEl = conversationLink.querySelector('.last-message');
                if (lastMessageEl) {
                    const messageContent = data.message.content;
                    lastMessageEl.textContent = messageContent.length > 60 ? messageContent.substring(0, 60) + '...' : messageContent;
                }
                
                if (isReceiver) {
                    const unreadBadge = conversationLink.querySelector('.unread-badge');
                    if (unreadBadge) {
                        const currentUnread = parseInt(unreadBadge.textContent) || 0;
                        unreadBadge.textContent = currentUnread + 1;
                    } else {
                        const badge = document.createElement('span');
                        badge.className = 'badge bg-primary rounded-pill unread-badge';
                        badge.textContent = '1';
                        conversationLink.querySelector('h5').appendChild(badge);
                    }
                }
                
                const conversationsList = document.getElementById('conversations-list');
                if (conversationsList && conversationLink.parentNode) {
                    conversationsList.insertBefore(conversationLink, conversationsList.firstChild);
                }
            }
        });
    });
</script>
@endif
@endpush

@endsection

