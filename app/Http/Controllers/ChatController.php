<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\Listing;
use App\Models\Booking;
use App\Models\Notification;
use App\Events\NotificationCreated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ChatController extends Controller
{
    /**
     * Show list of conversations
     */
    public function index(): View
    {
        $userId = Auth::id();
        
        $conversations = Conversation::where('guest_id', $userId)
            ->orWhere('host_id', $userId)
            ->with(['guest', 'host', 'listing', 'booking.availability.listing'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($conversation) use ($userId) {
                $isGuest = $conversation->guest_id === $userId;
                $otherUser = $isGuest ? $conversation->host : $conversation->guest;
                
                if ($conversation->booking_id) {
                    $booking = $conversation->booking;
                    $listing = $booking->availability->listing;
                    
                    if ($isGuest) {
                        $title = "Booking of {$listing->title} - " . $booking->check_in_date->format('M d, Y');
                    } else {
                        $title = "Reservation of {$listing->title} at " . $booking->check_in_date->format('M d, Y');
                    }
                } else {
                    $listing = $conversation->listing;
                    if ($isGuest) {
                        $title = $listing->title;
                    } else {
                        $title = "{$listing->title} - {$conversation->guest->full_name}";
                    }
                }
                
                $lastMessage = \App\Models\Message::where('conversation_id', $conversation->id)
                    ->orderBy('send_at', 'desc')
                    ->orderBy('id', 'desc')
                    ->first();
                
                $unreadCount = $conversation->unreadMessagesCount($userId);
                
                return [
                    'id' => $conversation->id,
                    'title' => $title,
                    'other_user' => $otherUser,
                    'last_message' => $lastMessage,
                    'unread_count' => $unreadCount,
                    'is_booking' => $conversation->booking_id !== null,
                    'booking' => $conversation->booking,
                    'listing' => $conversation->listing ?? ($conversation->booking ? $conversation->booking->availability->listing : null),
                    'created_at' => $conversation->created_at,
                    'is_guest' => $isGuest,
                ];
            });
        
        return view('pages.chat.index', compact('conversations'));
    }

    /**
     * Show a specific conversation
     */
    public function show(Conversation $conversation): View
    {
        $userId = Auth::id();
        
        // Verify user is part of this conversation
        if ($conversation->guest_id !== $userId && $conversation->host_id !== $userId) {
            abort(403);
        }
        
        $isGuest = $conversation->guest_id === $userId;
        $otherUser = $isGuest ? $conversation->host : $conversation->guest;
        
        if ($conversation->booking_id) {
            $booking = $conversation->booking;
            $listing = $booking->availability->listing;
            
            if ($isGuest) {
                $title = "Booking of {$listing->title} - " . $booking->check_in_date->format('M d, Y');
            } else {
                $title = "Reservation of {$listing->title} at " . $booking->check_in_date->format('M d, Y');
            }
        } else {
            $listing = $conversation->listing;
            if ($isGuest) {
                $title = $listing->title;
            } else {
                $title = "{$listing->title} - {$conversation->guest->full_name}";
            }
        }
        
        $author = $isGuest ? 'Host' : 'Guest';
        Message::where('conversation_id', $conversation->id)
            ->where('author', $author)
            ->where('is_read', false)
            ->update(['is_read' => true]);
        
        $messages = $conversation->messages()->orderBy('send_at', 'asc')->get();
        
        $bookingDetails = null;
        if ($conversation->booking_id) {
            $booking = $conversation->booking;
            $listing = $booking->availability->listing;
            $bookingDetails = [
                'booking_id' => $booking->id,
                'listing_title' => $listing->title,
                'check_in_date' => $booking->check_in_date->format('M d, Y'),
                'check_out_date' => $booking->check_out_date->format('M d, Y'),
                'num_guests' => $booking->num_guests,
                'total_price' => number_format($booking->total_price, 2),
                'status' => $booking->status,
                'listing_address' => $listing->location_address,
            ];
        }
        
        return view('pages.chat.show', compact('conversation', 'messages', 'isGuest', 'otherUser', 'title', 'bookingDetails'));
    }

    /**
     * Create or get existing conversation
     */
    public function create(Request $request)
    {
        $validated = $request->validate([
            'listing_id' => 'nullable|exists:listings,id',
            'booking_id' => 'nullable|exists:bookings,id',
        ]);
        
        $userId = Auth::id();
        
        if (!empty($validated['booking_id'])) {
            $booking = Booking::findOrFail($validated['booking_id']);
            
            $listing = $booking->availability->listing;
            $hostId = $listing->host_id;
            
            if ($booking->guest_id !== $userId && $hostId !== $userId) {
                return back()->withErrors(['error' => 'Unauthorized']);
            }
            
            $guestId = $booking->guest_id;
            
            $conversation = Conversation::where('guest_id', $guestId)
                ->where('host_id', $hostId)
                ->where('booking_id', $validated['booking_id'])
                ->first();
            
            if (!$conversation) {
                $conversation = Conversation::create([
                    'guest_id' => $guestId,
                    'host_id' => $hostId,
                    'booking_id' => $validated['booking_id'],
                    'listing_id' => null,
                ]);
            }
        } else {
            $listing = Listing::findOrFail($validated['listing_id']);
            $hostId = $listing->host_id;
            
            if ($hostId === $userId) {
                return response()->json(['error' => 'You cannot chat with your own listing'], 400);
            }
            
            $conversation = Conversation::where('guest_id', $userId)
                ->where('host_id', $hostId)
                ->where('listing_id', $validated['listing_id'])
                ->first();
            
            if (!$conversation) {
                $conversation = Conversation::create([
                    'guest_id' => $userId,
                    'host_id' => $hostId,
                    'listing_id' => $validated['listing_id'],
                    'booking_id' => null,
                ]);
            }
        }
        
        return redirect()->route('chat.show', $conversation->id);
    }

    /**
     * Send a message
     */
    public function sendMessage(Request $request, Conversation $conversation)
    {
        $userId = Auth::id();
        
        if ($conversation->guest_id !== $userId && $conversation->host_id !== $userId) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $validated = $request->validate([
            'content' => 'required|string|max:5000',
        ]);
        
        $isGuest = $conversation->guest_id === $userId;
        $author = $isGuest ? 'Guest' : 'Host';
        
        $message = Message::create([
            'conversation_id' => $conversation->id,
            'author' => $author,
            'content' => $validated['content'],
            'send_at' => now(),
            'is_read' => false,
        ]);
        
        $otherUserId = $isGuest ? $conversation->host_id : $conversation->guest_id;
        $notification = Notification::create([
            'type' => Notification::TYPE_MESSAGE_RECEIVED,
            'content' => 'You have a new message',
            'user_id' => $otherUserId,
            'message_id' => $message->id,
        ]);
        
        event(new NotificationCreated($notification));
        
        if (config('broadcasting.default') === 'pusher') {
            event(new \App\Events\MessageSent($message, $conversation));
        }
        
        return response()->json([
            'success' => true,
            'message' => [
                'id' => $message->id,
                'author' => $message->author,
                'content' => $message->content,
                'send_at' => $message->send_at->toIso8601String(),
                'conversation_id' => $message->conversation_id,
            ],
        ]);
    }

    /**
     * Mark messages as read
     */
    public function markAsRead(Conversation $conversation)
    {
        $userId = Auth::id();
        
        if ($conversation->guest_id !== $userId && $conversation->host_id !== $userId) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $isGuest = $conversation->guest_id === $userId;
        $author = $isGuest ? 'Host' : 'Guest';
        
        Message::where('conversation_id', $conversation->id)
            ->where('author', $author)
            ->where('is_read', false)
            ->update(['is_read' => true]);
        
        return response()->json(['success' => true]);
    }
}

