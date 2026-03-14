<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\EmailVerificationCode;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();
        unset($validated['current_password']);
        $user->fill($validated);

        $emailChanged = $user->isDirty('email');
        if ($emailChanged) {
            $user->email_verified_at = null;
        }

        try {
            $user->save();
        } catch (\Throwable $e) {
            Log::error('Profile update save failed', ['user_id' => $user->id, 'error' => $e->getMessage()]);
            return Redirect::route('profile.edit')
                ->withInput()
                ->with('error', 'Could not save your profile. Please try again or contact support.');
        }

        if ($emailChanged) {
            $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            EmailVerificationCode::where('user_id', $user->id)->whereNull('used_at')->update(['used_at' => now()]);
            EmailVerificationCode::create([
                'user_id' => $user->id,
                'code_hash' => Hash::make($code),
                'expires_at' => now()->addMinutes(15),
            ]);

            $from = mail_from();

            try {
                Mail::raw(
                    "Your VerityTrade verification code is: {$code}\n\nThis code expires in 15 minutes.",
                    function ($message) use ($user, $from): void {
                        $message->to($user->email, $user->name)->subject('Verify your updated email');
                        $message->from($from['address'], $from['name']);
                    }
                );
            } catch (\Throwable $e) {
                Log::error('Failed to send OTP after email change.', ['user_id' => $user->id, 'error' => $e->getMessage()]);
            }

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return Redirect::route('verification.code', ['email' => $user->email])
                ->with('status', 'Email updated. Verify the new email with the OTP to continue.');
        }

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->forceDelete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
