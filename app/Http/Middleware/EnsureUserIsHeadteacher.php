<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsHeadteacher
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // This middleware runs after authentication, so user should be authenticated
        // Just check if they have the headteacher role
        if (!auth()->check() || !auth()->user()->isHeadteacher()) {
            abort(403, 'Unauthorized access. Headteacher role required.');
        }

        return $next($request);
    }
}
