<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordIsChanged
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->must_change_password) {
            // Allow accessing profile, password update, and logout
            if (! $request->routeIs('profile.*') && ! $request->routeIs('logout')) {
                return redirect()->route('profile.edit')->with(
                    'warning',
                    'Security Notice: You are using a default or temporary password. Please set a new personal password to continue using the system.'
                );
            }
        }

        return $next($request);
    }
}
