<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Saca del sistema a un usuario que el admin bloqueó mientras tenía la sesión
 * abierta (HU13). El login ya impide que entre de nuevo (LoginRequest).
 * Está registrado en el grupo «web» de bootstrap/app.php, así corre en todas
 * las páginas.
 */
class UsuarioNoBloqueado
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user !== null && $user->estaBloqueado()) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors(['email' => __('auth.blocked')]);
        }

        return $next($request);
    }
}
