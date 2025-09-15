<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {

        // Habilita Sanctum "stateful" para /api (SPA)
        $middleware->statefulApi();

        // Middlewares del grupo web que tu proyecto ya usa
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);

        // ===================== ALIASES DE MIDDLEWARE =====================
        // 👇👇👇  AGREGADO: alias para usar ->middleware('perm:...') en las rutas  👇👇👇
        $middleware->alias([
            'perm' => \App\Http\Middleware\CheckPermission::class, // 👈 agregado
        ]);
        // =================================================================
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->create();
