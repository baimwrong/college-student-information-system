<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! $request->user()) {
            return redirect()->route('login');
        }

        if (empty($roles)) {
            return $next($request);
        }

        if (! in_array($request->user()->role, $roles, true)) {
            abort(403, 'Unauthorized. Your account does not have access to this resource.');
        }

        return $next($request);
    }
}
