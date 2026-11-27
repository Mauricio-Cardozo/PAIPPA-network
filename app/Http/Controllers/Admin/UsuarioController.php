<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Rol;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Gestión y bloqueo de usuarios (HU13). Un usuario bloqueado no puede iniciar
 * sesión y, si tenía una abierta, lo saca el middleware UsuarioNoBloqueado.
 */
class UsuarioController extends Controller
{
    public function index(Request $request): View
    {
        // tryFrom() descarta un rol inventado en la URL (?rol=xyz) sin dar error.
        $rol = Rol::tryFrom((string) $request->input('rol'));
        $buscar = trim((string) $request->input('buscar'));

        $usuarios = User::query()
            ->with('bloqueadoPor')
            ->when($buscar !== '', fn ($q) => $q->where(fn ($q) => $q
                ->where('name', 'like', '%'.$buscar.'%')
                ->orWhere('email', 'like', '%'.$buscar.'%')))
            ->when($rol !== null, fn ($q) => $q->where('rol', $rol->value))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.usuarios.index', [
            'usuarios' => $usuarios,
            'roles' => Rol::cases(),
        ]);
    }

    /** Bloquea al usuario y guarda quién lo bloqueó y cuándo (PAI-84). */
    public function bloquear(Request $request, User $usuario): RedirectResponse
    {
        if ($usuario->is($request->user())) {
            return back()->with('error', 'No podés bloquearte a vos mismo.');
        }

        if ($usuario->esAdmin()) {
            return back()->with('error', 'No se puede bloquear a otro administrador.');
        }

        // bloqueado_at y bloqueado_por no son fillable: se asignan a mano.
        $usuario->forceFill([
            'bloqueado_at' => now(),
            'bloqueado_por' => $request->user()->id,
        ])->save();

        return back()->with('exito', "{$usuario->name} fue bloqueado.");
    }

    /** Desbloquea al usuario y borra el registro del bloqueo. */
    public function desbloquear(User $usuario): RedirectResponse
    {
        $usuario->forceFill([
            'bloqueado_at' => null,
            'bloqueado_por' => null,
        ])->save();

        return back()->with('exito', "{$usuario->name} fue desbloqueado.");
    }
}
