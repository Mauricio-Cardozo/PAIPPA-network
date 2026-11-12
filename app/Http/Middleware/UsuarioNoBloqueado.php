<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Si un usuario es bloqueado mientras tiene la sesión abierta, en su
 * próximo pedido se le cierra la sesión. Está registrado en el grupo 'web'.
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
