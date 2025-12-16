<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Mail\MailModel;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ForgotPasswordController extends Controller
{
    /**
     * Show the forgot password form.
     */
    public function showForgotForm(): View
    {
        return view('pages.forgot_password');
    }

    /**
     * Send a password reset link to the given user.
     */
    public function sendResetLink(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', $request->email)->where('is_active', true)->first();

        if (!$user) {
            // For security, we don't reveal if the email exists or not
            return back()->with('status', 'If an account with that email exists, we have sent a password reset link.');
        }

        // Generate a unique token
        $token = Str::random(64);

        // Delete any existing tokens for this email
        DB::table('password_resets')->where('email', $request->email)->delete();

        // Store the hashed token in database
        DB::table('password_resets')->insert([
            'email' => $request->email,
            'token' => Hash::make($token),
            'created_at' => now(),
        ]);

        // Generate reset URL with the plain token (not hashed)
        $resetUrl = route('password.reset', ['token' => $token, 'email' => $request->email]);

        // Prepare mail data for the email template
        $mailData = [
            'name' => $user->full_name,
            'email' => $user->email,
            'resetUrl' => $resetUrl,
        ];

        try {
            Mail::to($user->email)->send(new MailModel($mailData));
            return back()->with('status', 'If an account with that email exists, we have sent a password reset link.');
        } catch (\Exception $e) {
            return back()->with('error', 'There was a problem sending the email. Please try again later.');
        }
    }

    /**
     * Show the password reset form.
     */
    public function showResetForm(Request $request, string $token): View|RedirectResponse
    {
        $email = $request->query('email');

        if (!$email) {
            return redirect()->route('password.request')->with('error', 'Invalid reset link.');
        }

        // Verify the token exists and is valid before showing the form
        $resetRecord = DB::table('password_resets')
            ->where('email', $email)
            ->first();

        if (!$resetRecord) {
            return redirect()->route('password.request')->with('error', 'This password reset link is invalid or has expired.');
        }

        // Check if token matches
        if (!Hash::check($token, $resetRecord->token)) {
            return redirect()->route('password.request')->with('error', 'This password reset link is invalid.');
        }

        // Check if token is expired (60 minutes)
        if (now()->diffInMinutes($resetRecord->created_at) > 60) {
            DB::table('password_resets')->where('email', $email)->delete();
            return redirect()->route('password.request')->with('error', 'This password reset link has expired. Please request a new one.');
        }

        return view('pages.reset_password', [
            'token' => $token,
            'email' => $email,
        ]);
    }

    /**
     * Reset the user's password.
     */
    public function resetPassword(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'min:8', 'confirmed'],
        ]);

        // Find the reset record
        $resetRecord = DB::table('password_resets')
            ->where('email', $request->email)
            ->first();

        if (!$resetRecord) {
            return back()->withErrors(['email' => 'This password reset link is invalid.']);
        }

        // Check if token is valid
        if (!Hash::check($request->token, $resetRecord->token)) {
            return back()->withErrors(['email' => 'This password reset link is invalid.']);
        }

        // Check if token is expired (60 minutes)
        if (now()->diffInMinutes($resetRecord->created_at) > 60) {
            DB::table('password_resets')->where('email', $request->email)->delete();
            return back()->withErrors(['email' => 'This password reset link has expired.']);
        }

        // Find and update the user
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'We could not find a user with that email address.']);
        }

        // Update the password with bcrypt hash
        $user->password_hash = Hash::make($request->password);
        $user->save();

        // Delete the reset token
        DB::table('password_resets')->where('email', $request->email)->delete();

        return redirect()->route('login')->with('status', 'Your password has been reset successfully! You can now log in with your new password.');
    }
}
