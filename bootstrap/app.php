<?php

use App\Http\Middleware\CabecerasSeguridad;
use App\Http\Middleware\SinCache;
use App\Http\Middleware\UsuarioNoBloqueado;
use App\Http\Middleware\VerificarRol;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Alias para usar en las rutas: ->middleware('rol:admin').
        $middleware->alias([
            'rol' => VerificarRol::class,
            'sin-cache' => SinCache::class,
        ]);

        // Cabeceras de seguridad en todas las respuestas web
        // y expulsión de los usuarios bloqueados que tenían la sesión abierta.
        $middleware->web(append: [
            CabecerasSeguridad::class,
            UsuarioNoBloqueado::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
