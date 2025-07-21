<?php
namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class ApiAuthenticate extends Middleware
{

    // protected function redirectTo($request)
    // {
    //     // Return JSON instead of redirect for API
    //     if ($request->expectsJson()) {
    //         abort(response()->json(['message' => 'Unauthenticated.'], 401));
    //     }
    // }
   protected function redirectTo($request)
{
    if ($request->expectsJson()) {
        return null; // Return null for API requests
    }
    
    return route('login'); // Only redirect web requests to login
}

}
