<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

use Illuminate\View\View;

use App\Models\User;

class RegisterController extends Controller
{
    /**
     * Show the user registration form.
     */
    public function showRegistrationForm(): View
    {
        // Render the registration view.
        return view('auth.register');
    }

    /**
     * Handle a new user registration request.
     *
     * This method:
     * - Validates the registration input data.
     * - Creates a new user with a hashed password.
     * - Logs the user in automatically after registration.
     * - Regenerates the session to prevent fixation attacks.
     * - Redirects the user to the cards page with a success message.
     */
    public function register(Request $request)
    {
        // Validate registration input.
        $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|min:8|confirmed',
            'date_of_birth' => 'required|date|before:-18 years',
            'phone' => ['required', 'string', 'regex:/^\+[0-9]{1,3}[0-9]{4,14}$/'],
        ]);

        // Create the new user.
        User::create([
            'full_name' => $request->full_name,
            'email' => $request->email,
            'password_hash' => Hash::make($request->password),
            'date_of_birth' => $request->date_of_birth,
            'phone' => $request->phone,
        ]);

        // Attempt login for the newly registered user.
        $credentials = $request->only('email', 'password');
        Auth::attempt($credentials);

        // Regenerate session for security (protection against session fixation).
        $request->session()->regenerate();

        // Redirect to cards page with a success message.
        return redirect()->route('cards.index')
            ->withSuccess('You have successfully registered & logged in!');
    }
}
