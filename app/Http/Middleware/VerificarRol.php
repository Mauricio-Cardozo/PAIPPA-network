<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Deja pasar solo a los usuarios con alguno de los roles indicados.
 * Uso en las rutas: ->middleware('rol:admin') o 'rol:admin,feriante'.
 */
class VerificarRol
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        // Sin sesión: lo manda al login (normalmente ya lo hizo el middleware 'auth').
        if ($user === null) {
            return redirect()->guest(route('login'));
        }

        if (! in_array($user->rol->value, $roles, true)) {
            abort(403, 'No tenés permiso para entrar a esta sección.');
        }

        return $next($request);
    }
}
