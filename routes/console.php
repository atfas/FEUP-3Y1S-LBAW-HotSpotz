<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Models\Booking;
use App\Models\Notification;
use App\Events\NotificationCreated;
use Carbon\Carbon;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule booking reminder for 9:00 
Schedule::call(function () {
    $tomorrow = Carbon::tomorrow()->toDateString();
    
    Booking::where('status', 'Confirmed')
        ->whereDate('check_in_date', $tomorrow)
        ->with(['availability.listing'])
        ->chunk(100, function ($bookings) {
            foreach ($bookings as $booking) {
                try {
                    $notification = Notification::create([
                        'type' => Notification::TYPE_REMAINDER,
                        'content' => "Reminder: Your stay at '{$booking->availability->listing->title}' begins tomorrow! Check-in: {$booking->check_in_date->format('M d, Y')}. Have a great stay!",
                        'user_id' => $booking->guest_id,
                        'booking_id' => $booking->id,
                    ]);
                    
                    event(new NotificationCreated($notification));
                } catch (\Exception $e) {
                    \Log::error("Failed to send booking reminder for booking #{$booking->id}: " . $e->getMessage());
                }
            }
        });
})->dailyAt('09:00')->name('send-booking-reminders')->withoutOverlapping();
