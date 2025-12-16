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

        // Store the plain token in database (for lookup by token)
        DB::table('password_resets')->insert([
            'email' => $request->email,
            'token' => $token,
            'created_at' => now(),
        ]);

        // Generate reset URL with only the token (email not exposed in URL)
        $resetUrl = route('password.reset', ['token' => $token]);

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
        // Look up the reset record by token (email is retrieved from DB, not URL)
        $resetRecord = DB::table('password_resets')
            ->where('token', $token)
            ->first();

        if (!$resetRecord) {
            return redirect()->route('password.request')->with('error', 'This password reset link is invalid or has expired.');
        }

        // Check if token is expired (60 minutes)
        if (now()->diffInMinutes($resetRecord->created_at) > 60) {
            DB::table('password_resets')->where('token', $token)->delete();
            return redirect()->route('password.request')->with('error', 'This password reset link has expired. Please request a new one.');
        }

        return view('pages.reset_password', [
            'token' => $token,
            'email' => $resetRecord->email,
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

        // Find the reset record by token
        $resetRecord = DB::table('password_resets')
            ->where('token', $request->token)
            ->first();

        if (!$resetRecord) {
            return back()->withErrors(['email' => 'This password reset link is invalid.']);
        }

        // Verify the email matches the token's associated email
        if ($resetRecord->email !== $request->email) {
            return back()->withErrors(['email' => 'This password reset link is invalid.']);
        }

        // Check if token is expired (60 minutes)
        if (now()->diffInMinutes($resetRecord->created_at) > 60) {
            DB::table('password_resets')->where('token', $request->token)->delete();
            return back()->withErrors(['email' => 'This password reset link has expired.']);
        }

        // Find and update the user
        $user = User::where('email', $resetRecord->email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'We could not find a user with that email address.']);
        }

        // Update the password with bcrypt hash
        $user->password_hash = Hash::make($request->password);
        $user->save();

        // Delete the reset token
        DB::table('password_resets')->where('token', $request->token)->delete();

        return redirect()->route('login')->with('status', 'Your password has been reset successfully! You can now log in with your new password.');
    }
}
