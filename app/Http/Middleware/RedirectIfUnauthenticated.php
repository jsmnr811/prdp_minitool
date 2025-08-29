<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfUnauthenticated

{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse  $next
     * @param  string|null  ...$guards
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, ...$guards)
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (!Auth::guard($guard)->check()) {
                // Redirect unauthenticated users based on the guard
                switch ($guard) {
                    case 'geomapping':
                        return redirect()->route('geomapping.iplan.login');
                    case null:
                    case 'web':
                        return redirect()->route('login'); // uses named route if available
                    default:
                        return redirect('/login');
                }
            }
        }

        return $next($request);
    }
}
