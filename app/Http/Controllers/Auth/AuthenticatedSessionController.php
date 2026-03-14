<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $user = Auth::user();

        // Hardcoded: require either email verification (OTP) or admin approval; either is enough to login.
        $requireEmailVerification = feature_enabled('require_email_verification', true);
        $requireAdminApproval = feature_enabled('require_admin_approval', true);

        $hasVerifiedEmail = (bool) ($user?->hasVerifiedEmail());
        $hasAdminApproval = (bool) ($user?->is_approved);

        $isAllowedByPolicy = true;
        if ($requireEmailVerification && $requireAdminApproval) {
            $isAllowedByPolicy = $hasVerifiedEmail || $hasAdminApproval;
        } elseif ($requireEmailVerification) {
            $isAllowedByPolicy = $hasVerifiedEmail;
        } elseif ($requireAdminApproval) {
            $isAllowedByPolicy = $hasAdminApproval;
        }

        if ($user && !$isAllowedByPolicy) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return back()->withErrors([
                'email' => 'Verify your email with the 6-digit code (check inbox/spam) or wait for admin approval to sign in.',
            ])->onlyInput('email');
        }

        $request->session()->regenerate();

        if ($user && ($user->hasRole('super_admin') || $user->hasRole('admin') || $user->hasRole('staff'))) {
            return redirect()->intended(route('admin.dashboard', absolute: false));
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
