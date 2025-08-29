<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
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
        if (Auth::guard($guard)->check()) {
            if ($guard === 'geomapping') {
                $user = Auth::guard('geomapping')->user();

                if ((int) $user->role === 1) {
                    return redirect()->route('investment.user-list');
                }

                return redirect()->route('geomapping.iplan.landing');
            }

            return redirect('/home');
        }
    }

    return $next($request);
}

}
