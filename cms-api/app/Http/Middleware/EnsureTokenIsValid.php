<?php
namespace App\Http\Middleware;

use Closure;
use Symfony\Component\HttpFoundation\Response;

class EnsureTokenIsValid
{
    public function handle($request, Closure $next)
    {
        if (! $request->bearerToken()) {
            return response()->json(['message' => 'Token missing'], 401);
        }

        $token = \Laravel\Sanctum\PersonalAccessToken::findToken($request->bearerToken());

        if (! $token) {
            return response()->json(['message' => 'Invalid token'], 401);
        }

        return $next($request);
    }
}
