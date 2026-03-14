<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PasswordResetOtp;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class PasswordOtpController extends Controller
{
    public function create(): View
    {
        return view('auth.forgot-password-otp');
    }

    public function send(Request $request): RedirectResponse
    {
        $request->merge([
            'email' => strtolower(trim((string) $request->input('email'))),
        ]);

        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', $validated['email'])->first();
        if (!$user) {
            return back()->with('status', 'If an account exists, a reset code has been sent. Check inbox and spam folder.');
        }

        $latest = PasswordResetOtp::where('user_id', $user->id)->latest('id')->first();
        if ($latest && $latest->created_at->gt(now()->subSeconds(60))) {
            return back()->withErrors(['email' => 'Please wait about 60 seconds before requesting another reset code.'])->withInput();
        }

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        PasswordResetOtp::where('user_id', $user->id)->whereNull('used_at')->update(['used_at' => now()]);

        PasswordResetOtp::create([
            'user_id' => $user->id,
            'code_hash' => Hash::make($code),
            'expires_at' => now()->addMinutes(15),
        ]);

        $from = mail_from();

        try {
            Mail::raw(
                "Your VerityTrade password reset code is: {$code}\n\nThis code expires in 15 minutes.",
                function ($message) use ($user, $from): void {
                    $message->to($user->email, $user->name)->subject('Your VerityTrade Password Reset Code');
                    $message->from($from['address'], $from['name']);
                }
            );
        } catch (\Throwable $e) {
            Log::error('Failed to send password reset OTP.', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors(['email' => 'Could not send reset code. Check mail settings.'])->withInput();
        }

        return redirect()->route('password.reset.otp', ['email' => $user->email])
            ->with('status', 'Reset code sent. Check inbox and spam folder.');
    }

    public function showReset(Request $request): View
    {
        return view('auth.reset-password-otp', [
            'email' => (string) $request->query('email', ''),
        ]);
    }

    public function reset(Request $request): RedirectResponse
    {
        $request->merge([
            'email' => strtolower(trim((string) $request->input('email'))),
        ]);

        $validated = $request->validate([
            'email' => ['required', 'email'],
            'code' => ['required', 'digits:6'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = User::where('email', $validated['email'])->first();
        if (!$user) {
            return back()->withErrors(['email' => 'No account found for this email.'])->withInput();
        }

        $otp = PasswordResetOtp::where('user_id', $user->id)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->latest('id')
            ->first();

        if (!$otp || !Hash::check($validated['code'], $otp->code_hash)) {
            return back()->withErrors(['code' => 'Invalid or expired reset code.'])->withInput();
        }

        $otp->update(['used_at' => now()]);
        $user->update(['password' => Hash::make($validated['password'])]);

        return redirect()->route('login')->with('status', 'Password reset successful. You can now log in.');
    }
}
