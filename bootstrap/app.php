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
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
            'can.create.documents' => \App\Http\Middleware\EnsureCanCreateDocuments::class,
            'single.session' => \App\Http\Middleware\SingleSessionMiddleware::class,
        ]);
        
        // Add single session check to web middleware
        $middleware->appendToGroup('web', \App\Http\Middleware\SingleSessionMiddleware::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
