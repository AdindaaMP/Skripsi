<?php

use Illuminate\Foundation\application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
         $middleware->alias([
        // Spatie Permission middleware removed - using custom CheckRole middleware
    ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
    })->create();
