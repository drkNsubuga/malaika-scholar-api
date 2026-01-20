<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Auth\AuthenticationException;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateApi
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated via Sanctum
        if (!$request->user()) {
            throw new AuthenticationException('Unauthenticated. Please provide a valid API token.');
        }

        // Check if user account is active
        if (!$request->user()->is_active) {
            return response()->json([
                'message' => 'Account is inactive. Please contact support.',
                'error' => 'account_inactive'
            ], 403);
        }

        return $next($request);
    }
}
