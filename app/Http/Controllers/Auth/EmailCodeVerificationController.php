<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\EmailVerificationCode;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class EmailCodeVerificationController extends Controller
{
    private function maskEmail(string $email): string
    {
        if (!str_contains($email, '@')) {
            return $email;
        }

        [$name, $domain] = explode('@', $email, 2);
        $visible = mb_substr($name, 0, 2);
        $masked = str_repeat('*', max(1, mb_strlen($name) - 2));

        return $visible . $masked . '@' . $domain;
    }

    public function show(Request $request): View
    {
        $email = strtolower(trim((string) $request->query('email', session('verification_email', ''))));
        $resendAvailableAt = 0;
        $maskedEmail = '';

        if ($email !== '') {
            $user = User::where('email', $email)->first();
            if ($user) {
                $latest = EmailVerificationCode::where('user_id', $user->id)->latest('id')->first();
                if ($latest) {
                    $resendAvailableAt = $latest->created_at->addSeconds(60)->timestamp;
                }
                $maskedEmail = $this->maskEmail($user->email);
            }
        }

        return view('auth.verify-code', [
            'email' => $email,
            'maskedEmail' => $maskedEmail,
            'resendAvailableAt' => $resendAvailableAt,
        ]);
    }

    public function verify(Request $request): RedirectResponse
    {
        $request->merge([
            'email' => strtolower(trim((string) $request->input('email'))),
        ]);

        $validated = $request->validate([
            'email' => ['required', 'email'],
            'code' => ['required', 'digits:6'],
        ]);

        $user = User::where('email', $validated['email'])->first();
        if (!$user) {
            return back()->withErrors(['email' => 'No account found for this email.'])->withInput();
        }

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('login')->with('status', 'Email is already verified. You can log in.');
        }

        $latestCode = EmailVerificationCode::where('user_id', $user->id)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->latest('id')
            ->first();

        if (!$latestCode || !Hash::check($validated['code'], $latestCode->code_hash)) {
            return back()->withErrors(['code' => 'Invalid or expired verification code.'])->withInput();
        }

        $latestCode->update(['used_at' => now()]);
        $user->forceFill(['email_verified_at' => now()])->save();

        return redirect()->route('login')->with('status', 'Email verified successfully. You can now log in.');
    }

    public function resend(Request $request): RedirectResponse
    {
        $request->merge([
            'email' => strtolower(trim((string) $request->input('email'))),
        ]);

        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', $validated['email'])->first();
        if (!$user) {
            return back()->with('status', 'If an account exists, a new code has been sent. Check inbox and spam folder.');
        }

        if ($user->hasVerifiedEmail()) {
            return back()->with('status', 'Email is already verified.');
        }

        $latest = EmailVerificationCode::where('user_id', $user->id)->latest('id')->first();
        if ($latest && $latest->created_at->gt(now()->subSeconds(60))) {
            return back()->withErrors([
                'code' => 'Please wait about 60 seconds before requesting another code.',
            ])->withInput();
        }

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        EmailVerificationCode::where('user_id', $user->id)
            ->whereNull('used_at')
            ->update(['used_at' => now()]);

        EmailVerificationCode::create([
            'user_id' => $user->id,
            'code_hash' => Hash::make($code),
            'expires_at' => now()->addMinutes(15),
        ]);

        $fromName = (string) site_setting('mail_from_name', config('mail.from.name'));
        $fromAddress = (string) site_setting('mail_from_address', config('mail.from.address'));

        try {
            Mail::raw(
                "Your VerityTrade verification code is: {$code}\n\nThis code expires in 15 minutes.",
                function ($message) use ($user, $fromAddress, $fromName): void {
                    $message->to($user->email, $user->name)->subject('Your VerityTrade Verification Code');
                    if ($fromAddress) {
                        $message->from($fromAddress, $fromName ?: 'VerityTrade');
                    }
                }
            );
        } catch (\Throwable $e) {
            Log::error('Failed to resend verification code.', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors([
                'email' => 'Could not send email right now. Please check mail settings.',
            ])->withInput();
        }

        return back()
            ->with('status', 'A new verification code has been sent. Check inbox and spam folder.')
            ->with('verification_email', $user->email);
    }
}
