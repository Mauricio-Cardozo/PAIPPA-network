<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Evita que el navegador guarde en caché las páginas privadas.
 * Sin esto, después de cerrar sesión, el botón Atrás mostraría la página
 * privada guardada en el navegador.
 */
class SinCache
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('Cache-Control', 'no-store, private');
        $response->headers->set('Pragma', 'no-cache');

        return $response;
    }
}
