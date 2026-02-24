<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Permission Middleware
|--------------------------------------------------------------------------
| Restricts access based on permissions.
|--------------------------------------------------------------------------
*/

class PermissionMiddleware
{
    public function handle(Request $request, Closure $next, $permission)
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        if (!auth()->user()->hasPermission($permission)) {
            abort(403, 'Permission denied.');
        }

        return $next($request);
    }
}
