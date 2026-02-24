<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| EnsureUserIsApproved Middleware
|--------------------------------------------------------------------------
| Prevents users from accessing dashboard if not approved by admin.
|--------------------------------------------------------------------------
*/

class EnsureUserIsApproved
{
    public function handle(Request $request, Closure $next)
    {
        // If user is logged in but not approved
        if (auth()->check() && !auth()->user()->is_approved) {

            // Logout user
            auth()->logout();

            return redirect()->route('login')
                ->with('error', 'Your account is awaiting approval.');
        }

        return $next($request);
    }
}
