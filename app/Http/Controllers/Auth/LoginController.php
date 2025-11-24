<?php
 
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{

    /**
     * Show the login form.
     *
     * If the user is already authenticated, redirect them
     * to the cards dashboard instead of showing the form.
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('home');
        } else {
            return view('auth.login');
        }
    }

    /**
     * Process an authentication attempt.
     *
     * Validates the incoming request, checks the provided
     * credentials, and logs the user in if successful.
     * The session is regenerated to protect against session fixation.
     */
    public function authenticate(Request $request): RedirectResponse
    {
        // Validate the request data.
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Find user by email
        $user = \App\Models\User::where('email', $request->email)->first();

        if ($user) {
            // Check if user is active and not blocked
            if (!$user->is_active || $user->is_blocked) {
                return back()->withErrors([
                    'email' => 'Your account is not active or has been blocked.',
                ])->onlyInput('email');
            }

            // Check for plain text password match (legacy/seeder support)
            if ($user->password_hash === $request->password) {
                Auth::login($user, $request->filled('remember'));
                $request->session()->regenerate();
                return redirect()->intended(route('home'));
            }

            // Check for Bcrypt hash match (standard Laravel auth)
            if (\Illuminate\Support\Facades\Hash::check($request->password, $user->password_hash)) {
                Auth::login($user, $request->filled('remember'));
                $request->session()->regenerate();
                return redirect()->intended(route('home'));
            }
        }

        // Authentication failed: return back with an error message.
        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }
}
