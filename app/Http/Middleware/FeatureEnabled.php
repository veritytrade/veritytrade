<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class FeatureEnabled
{
    public function handle(Request $request, Closure $next, string $key)
    {
        if (!feature_enabled($key, true)) {
            abort(404);
        }

        return $next($request);
    }
}
