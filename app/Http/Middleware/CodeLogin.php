<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CodeLogin
{
    /**
     * Handle an incoming request.
     */
  public function handle(Request $request, Closure $next)
    {
        if (!$request->session()->has('custom_logged_in')) {
            return redirect()->route('investment-forum'); 
        }

        return $next($request);
    }
}
