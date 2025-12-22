@extends('layouts.app')

@section('content')
<div class="container mt-5 pb-5">
    <!-- Breadcrumbs -->
    @php
        $breadcrumbs = [
            ['title' => 'Messages', 'url' => route('chat.index')],
        ];
        if (isset($conversation) && $conversation->listing) {
            $breadcrumbs[] = ['title' => $conversation->listing->title, 'url' => route('listing.show', $conversation->listing->id)];
        }
        $breadcrumbs[] = ['title' => 'Chat with ' . $otherUser->full_name, 'url' => null];
    @endphp

    @include('partials.page_header', [
        'breadcrumbs' => $breadcrumbs
    ])
    <div class="row">
        <div class="col-12 col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold text-wine">{{ optional($conversation->listing)->title ?? 'Listing' }} - {{ $otherUser->full_name }}</h5>
                    <a href="{{ route('chat.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fa-solid fa-arrow-left"></i> Back to messages
                    </a>
                </div>
                <div class="card-body p-0" style="height: 600px; display: flex; flex-direction: column;">
                    <!-- Messages Area -->
                    <div id="messages-container" class="flex-grow-1 p-3 overflow-auto" style="max-height: 500px;">
                        @foreach($messages as $message)
                            <div class="mb-3 d-flex {{ $message->author === ($isGuest ? 'Guest' : 'Host') ? 'justify-content-end' : 'justify-content-start' }}" data-message-id="{{ $message->id }}">
                                <div class="message-bubble {{ $message->author === ($isGuest ? 'Guest' : 'Host') ? 'bg-wine text-white' : 'bg-secondary text-white' }}" 
                                     style="max-width: 70%; padding: 10px 15px; border-radius: 18px;">
                                    <p class="mb-1">{{ $message->content }}</p>
                                    <small class="{{ $message->author === ($isGuest ? 'Guest' : 'Host') ? 'text-white-50' : 'text-muted text-white-50' }}">
                                        {{ $message->send_at->format('M d, H:i') }}
                                    </small>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    <!-- Message Input -->
                    <div class="border-top p-3">
                        <form id="message-form" class="d-flex gap-2">
                            @csrf
                            <input type="text" 
                                   id="message-input" 
                                   class="form-control" 
                                   placeholder="Type your message..." 
                                   required
                                   autocomplete="off">
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="fa-solid fa-paper-plane"></i> Send
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Details Sidebar -->
        <div class="col-12 col-lg-4 mt-4 mt-lg-0">
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold text-wine"><i class="fa-solid fa-info-circle"></i> Details</h5>
                </div>
                <div class="card-body">
                    @if($bookingDetails)
                        <h6>Booking Information</h6>
                        <hr>
                        <p><strong>Listing:</strong> {{ $bookingDetails['listing_title'] }}</p>
                        <p><strong>Address:</strong> {{ $bookingDetails['listing_address'] }}</p>
                        <p><strong>Check-in:</strong> {{ $bookingDetails['check_in_date'] }}</p>
                        <p><strong>Check-out:</strong> {{ $bookingDetails['check_out_date'] }}</p>
                        <p><strong>Guests:</strong> {{ $bookingDetails['num_guests'] }}</p>
                        <p><strong>Total Price:</strong> ${{ $bookingDetails['total_price'] }}</p>
                        <p><strong>Status:</strong> 
                            <span class="badge bg-{{ $bookingDetails['status'] === 'Confirmed' ? 'success' : ($bookingDetails['status'] === 'Cancelled' ? 'danger' : 'warning') }}">
                                {{ $bookingDetails['status'] }}
                            </span>
                        </p>
                        <a href="{{ route('bookings.index') }}" class="btn btn-sm btn-outline-primary mt-2">
                            View Booking
                        </a>
                    @else
                        <h6>Listing Information</h6>
                        <hr>
                        <p><strong>Title:</strong> {{ optional($conversation->listing)->title ?? 'Listing not available' }}</p>
                        <p><strong>Address:</strong> {{ optional($conversation->listing)->location_address ?? '—' }}</p>
                        @if(isset($conversation) && $conversation->listing)
                            <a href="{{ route('listing.show', $conversation->listing->id) }}" class="btn btn-sm btn-primary mt-2">
                                View Listing
                            </a>
                        @else
                            <a href="{{ route('bookings.index') }}" class="btn btn-sm btn-secondary mt-2">
                                View Bookings
                            </a>
                        @endif
                    @endif
                    
                    <hr>
                    <h6>Conversation Partner</h6>
                    <div class="d-flex align-items-center gap-2">
                        @if($otherUser->profile_picture_url)
                            <img src="{{ $otherUser->profile_picture_url }}" 
                                 alt="{{ $otherUser->full_name }}" 
                                 class="rounded-circle" 
                                 style="width: 40px; height: 40px; object-fit: cover;">
                        @else
                            <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center" 
                                 style="width: 40px; height: 40px;">
                                <i class="fa-solid fa-user text-white"></i>
                            </div>
                        @endif
                        <div>
                            <strong>{{ $otherUser->full_name }}</strong>
                            <br>
                            <small class="text-muted">{{ $isGuest ? 'Host' : 'Guest' }}</small>
                        </div>
                    </div>
                    <a href="{{ route('profile.public', $otherUser->id) }}" class="btn btn-sm btn-secondary mt-2">
                        View Profile
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const conversationId = {{ $conversation->id }};
        const userId = {{ Auth::id() }};
        const isGuest = {{ $isGuest ? 'true' : 'false' }};
        
        // Initialize Pusher
        @if(config('broadcasting.default') === 'pusher')
        const pusherKey = '{{ config('broadcasting.connections.pusher.key') }}';
        if (!pusherKey) {
            console.warn('Pusher key is not configured. Please set PUSHER_APP_KEY in your .env and run php artisan config:clear');
        } else {
            const pusher = new Pusher(pusherKey, {
                cluster: '{{ config('broadcasting.connections.pusher.options.cluster') }}',
                encrypted: true,
                authEndpoint: '/broadcasting/auth',
                auth: {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                }
            });

            // Subscribe to user's private channel
            const channel = pusher.subscribe('private-user.' + userId);

            // Listen for new messages
            channel.bind('message.sent', function(data) {
                if (data.conversation_id === conversationId) {
                    const existingMessage = document.querySelector(`[data-message-id="${data.message.id}"]`);
                    if (!existingMessage) {
                        addMessageToChat(data.message);
                        scrollToBottom();
                    }
                }
            });
        }
        @endif
        
        // Message form submission
        const messageForm = document.getElementById('message-form');
        if (!messageForm) {
            console.error('Message form not found');
            return;
        }
        
        messageForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const input = document.getElementById('message-input');
            const content = input.value.trim();
            
            if (!content) return;
            
            const formData = new FormData();
            formData.append('content', content);
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
            
            try {
                const response = await fetch(`/chat/${conversationId}/message`, {
                    method: 'POST',
                    body: formData,
                });
                
                const data = await response.json();
                
                if (response.ok) {
                    input.value = '';
                    // Always add message immediately for sender (optimistic update)
                    // Pusher will add it for receiver
                    if (data.message) {
                        addMessageToChat(data.message);
                        scrollToBottom();
                    }
                } else {
                    alert('Failed to send message: ' + (data.error || 'Unknown error'));
                }
            } catch (error) {
                console.error('Error sending message:', error);
                alert('Failed to send message');
            }
        });
        
        function addMessageToChat(message) {
            const container = document.getElementById('messages-container');
            if (!container) return;
            
            // Check if message already exists (avoid duplicates)
            if (message.id && document.querySelector(`[data-message-id="${message.id}"]`)) {
                return;
            }
            
            const messageDiv = document.createElement('div');
            if (message.id) {
                messageDiv.setAttribute('data-message-id', message.id);
            }
            messageDiv.className = 'mb-3 d-flex ' + (message.author === (isGuest ? 'Guest' : 'Host') ? 'justify-content-end' : 'justify-content-start');
            
            const bubble = document.createElement('div');
            bubble.className = 'message-bubble ' + (message.author === (isGuest ? 'Guest' : 'Host') ? 'bg-wine text-white' : 'bg-light');
            bubble.style.cssText = 'max-width: 70%; padding: 10px 15px; border-radius: 18px;';
            
            const date = new Date(message.send_at);
            bubble.innerHTML = `
                <p class="mb-1">${escapeHtml(message.content)}</p>
                <small class="${message.author === (isGuest ? 'Guest' : 'Host') ? 'text-white-50' : 'text-muted'}">
                    ${formatDate(date)}
                </small>
            `;
            
            messageDiv.appendChild(bubble);
            container.appendChild(messageDiv);
        }
        
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        function formatDate(date) {
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            const month = months[date.getMonth()];
            const day = date.getDate();
            const hours = date.getHours().toString().padStart(2, '0');
            const minutes = date.getMinutes().toString().padStart(2, '0');
            return `${month} ${day}, ${hours}:${minutes}`;
        }
        
        function scrollToBottom() {
            const container = document.getElementById('messages-container');
            if (container) {
                container.scrollTop = container.scrollHeight;
            }
        }
        
        // Scroll to bottom on load
        scrollToBottom();
    });
</script>
@endpush

@endsection
