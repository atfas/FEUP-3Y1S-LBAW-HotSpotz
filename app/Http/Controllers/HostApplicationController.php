<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class HostApplicationController extends Controller
{
    /**
     * Show the host application form.
     */
    public function create(): View
    {
        $user = Auth::user();
        
        // Redirect if user already has an application or is approved
        if ($user->application_status !== null) {
            return redirect()->route('profile.show')
                ->with('error', 'You have already submitted a host application.');
        }

        return view('pages.host_application', ['user' => $user]);
    }

    /**
     * Store the host application.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();
        
        // Check if user already has an application
        if ($user->application_status !== null) {
            return redirect()->route('profile.show')
                ->with('error', 'You have already submitted a host application.');
        }

        $validated = $request->validate([
            'answers' => ['required', 'string', 'min:50', 'max:2000'],
            'verification_docs' => ['required', 'string', 'max:500'],
            'payout_details' => ['required', 'string', 'max:500'],
        ]);

        // Update user with application data
        $user->answers = $validated['answers'];
        $user->verification_docs = $validated['verification_docs'];
        $user->payout_details = $validated['payout_details'];
        $user->application_status = 'Pending';
        $user->submitted_at = now();
        $user->save();

        return redirect()->route('profile.show')
            ->with('success', 'Your host application has been submitted successfully! We will review it shortly.');
    }
}
