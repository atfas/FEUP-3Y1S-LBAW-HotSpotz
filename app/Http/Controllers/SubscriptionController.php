<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class SubscriptionController extends Controller
{
    /**
     * Cancel the user's subscription.
     */
    public function cancel(Request $request): RedirectResponse
    {
        $user = Auth::user();
        
        if (!$user->subscription) {
            return redirect()->route('profile.show')->with('error', 'You do not have an active subscription.');
        }

        $subscription = $user->subscription;
        $subscription->member_status = 'Cancelled';
        $subscription->cancelled_at = now();
        $subscription->save();

        return redirect()->route('profile.show')->with('success', 'Your subscription has been cancelled.');
    }
}
