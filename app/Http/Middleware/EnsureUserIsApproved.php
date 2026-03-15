<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| EnsureUserIsApproved Middleware
|--------------------------------------------------------------------------
| Allows dashboard access if EITHER email is verified (OTP) OR admin has approved,
| according to feature flags (require_email_verification, require_admin_approval).
|--------------------------------------------------------------------------
*/

class EnsureUserIsApproved
{
    public function handle(Request $request, Closure $next)
    {
        if (! auth()->check()) {
            return $next($request);
        }

        $user = auth()->user();
        $requireEmailVerification = function_exists('feature_enabled') ? feature_enabled('require_email_verification', true) : true;
        $requireAdminApproval = function_exists('feature_enabled') ? feature_enabled('require_admin_approval', true) : true;

        $hasVerifiedEmail = (bool) $user->hasVerifiedEmail();
        $hasAdminApproval = (bool) $user->is_approved;

        $allowed = true;
        if ($requireEmailVerification && $requireAdminApproval) {
            $allowed = $hasVerifiedEmail || $hasAdminApproval;
        } elseif ($requireEmailVerification) {
            $allowed = $hasVerifiedEmail;
        } elseif ($requireAdminApproval) {
            $allowed = $hasAdminApproval;
        }

        if (! $allowed) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->with('error', 'Verify your email with the 6-digit code (check inbox/spam) or wait for admin approval to sign in.');
        }

        return $next($request);
    }
}
