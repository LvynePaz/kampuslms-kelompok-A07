<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

# ini ngurus routing, nyambungin url ke controller
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
# ini ngurus middleware 
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
# ini ngurus exception
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
