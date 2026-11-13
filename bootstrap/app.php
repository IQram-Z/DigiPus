<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias(['role' => \App\Http\Middleware\EnsureRole::class]);
        // Apple mengirim callback login lewat POST dari situs lain (response_mode=form_post)
        $middleware->validateCsrfTokens(except: ['auth/apple/callback']);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
