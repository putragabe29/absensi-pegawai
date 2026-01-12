<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {

        // ===== REGISTER ALIAS MIDDLEWARE =====
        $middleware->alias([
            'session.expired' => \App\Http\Middleware\SessionExpired::class,
        ]);

    })
    ->withExceptions(function ($exceptions) {
        //
    })
    ->create();
