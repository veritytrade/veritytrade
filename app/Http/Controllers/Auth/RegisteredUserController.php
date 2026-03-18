<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\EmailVerificationCode;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $normalizedEmail = strtolower(trim((string) $request->input('email')));

        $validated = $request->validate([
            'username' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique('users', 'email')->whereNull('deleted_at'),
            ],
            'phone' => ['required', 'regex:/^\+?[0-9]{6,20}$/'],
            'state' => ['required', 'string', 'max:100'],
            'city' => ['required', 'string', 'max:100'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'phone.regex' => 'Enter a valid phone number (digits only, 6–20 characters, optional + prefix).',
            'state.required' => 'Please enter your state.',
            'city.required' => 'Please enter your city.',
        ]);

        $normalizedPhone = preg_replace('/[^\d+]/', '', (string) $validated['phone']);
        $state = trim((string) $validated['state']);
        $city = trim((string) $validated['city']);

        $requiresAdminApproval = feature_enabled('require_admin_approval', true);

        $existingUser = User::withTrashed()->where('email', $normalizedEmail)->first();

        if ($existingUser && $existingUser->trashed()) {
            $existingUser->restore();
            $existingUser->forceFill([
                'name' => $request->name,
                'username' => $request->username,
                'email' => $normalizedEmail,
                'phone' => $normalizedPhone,
                'state' => $state,
                'city' => $city,
                'password' => Hash::make($request->password),
                'is_approved' => !$requiresAdminApproval,
                'approved_at' => !$requiresAdminApproval ? now() : null,
                'approved_by' => null,
                'email_verified_at' => null,
            ])->save();

            $user = $existingUser;
        } else {
            $user = User::create([
                'name' => $request->name,
                'username' => $request->username,
                'email' => $normalizedEmail,
                'phone' => $normalizedPhone,
                'state' => $state,
                'city' => $city,
                'password' => Hash::make($request->password),
                'is_approved' => !$requiresAdminApproval,
                'approved_at' => !$requiresAdminApproval ? now() : null,
            ]);
        }

        if ($customerRole = Role::where('name', 'customer')->first()) {
            $user->assignRole($customerRole);
        }

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
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
                    $message->to($user->email, $user->name)->subject('Your VerityTrade Verification Code');
                    $message->from($from['address'], $from['name']);
                }
            );
        } catch (\Throwable $e) {
            Log::error('Failed to send verification code after registration.', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $e->getMessage(),
            ]);
        }

        return redirect()->route('verification.code', ['email' => $user->email], 303)
            ->with('status', 'Registration successful. Check your inbox and spam folder for the 6-digit code.')
            ->with('verification_email', $user->email);
    }
}
