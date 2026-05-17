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
        // ★ DAFTARKAN MIDDLEWARE CUSTOM KITA
        $middleware->alias([
            'auth' => \App\Http\Middleware\Authenticate::class,
        ]);
    })
        ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'auth' => \App\Http\Middleware\Authenticate::class,
            'admin' => \App\Http\Middleware\AdminMiddleware::class,  // ★ PAKAI YANG INI
        ]);
    })
    
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
