<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureIngestionApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $configuredKey = (string) config('services.ingestion.api_key', '');
        if ($configuredKey === '') {
            abort(500, 'Ingestion API key is not configured.');
        }

        $providedKey = (string) $request->header('X-API-Key', '');
        if ($providedKey === '') {
            $authHeader = (string) $request->header('Authorization', '');
            if (str_starts_with($authHeader, 'Bearer ')) {
                $providedKey = trim(substr($authHeader, 7));
            }
        }

        if (! hash_equals($configuredKey, $providedKey)) {
            abort(401, 'Invalid ingestion API key.');
        }

        return $next($request);
    }
}
