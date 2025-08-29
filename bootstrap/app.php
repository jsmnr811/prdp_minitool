<?php

use Illuminate\Foundation\Application;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Http\Middleware\RedirectIfUnauthenticated;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Register your route middleware alias here
        $middleware->alias([
            'guest-geo' => RedirectIfAuthenticated::class,
            'auth-geo'  => RedirectIfUnauthenticated::class,
        ]);

        // Example if you need route-specific middleware:
        // $middleware->route('auth', \App\Http\Middleware\Authenticate::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->create();   // ✅ must end on its own line
