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
    ->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'admin' => \App\Http\Middleware\EnsureAdmin::class,
    ]);

    // Exclure le webhook Kkiapay de la vérification CSRF
    $middleware->validateCsrfTokens(except: [
        'api/kkiapay/*',
    ]);

})
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();