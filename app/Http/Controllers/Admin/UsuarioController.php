<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Rol;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Gestión de usuarios (HU13): listado, búsqueda y bloqueo.
 * Un bloqueado no puede iniciar sesión, y si la tenía abierta lo saca
 * el middleware UsuarioNoBloqueado.
 */
class UsuarioController extends Controller
{
    public function index(Request $request): View
    {
        $request->validate([
            'rol' => ['nullable', Rule::enum(Rol::class)],
        ]);

        $usuarios = User::query()
            ->with('bloqueadoPor:id,name')
            ->when($request->filled('buscar'), function ($q) use ($request) {
                $texto = '%'.$request->input('buscar').'%';
                $q->where(fn ($q) => $q->where('name', 'like', $texto)->orWhere('email', 'like', $texto));
            })
            ->when($request->filled('rol'), fn ($q) => $q->where('rol', $request->input('rol')))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.usuarios.index', ['usuarios' => $usuarios, 'roles' => Rol::cases()]);
    }

    public function bloquear(Request $request, User $usuario): RedirectResponse
    {
        if ($usuario->is($request->user())) {
            return back()->with('error', 'No podés bloquearte a vos mismo.');
        }

        if ($usuario->esAdmin()) {
            return back()->with('error', 'No se puede bloquear a otro administrador.');
        }

        // Se guarda quién bloqueó y cuándo (PAI-84).
        $usuario->forceFill([
            'bloqueado_at' => now(),
            'bloqueado_por' => $request->user()->id,
        ])->save();

        return back()->with('exito', "{$usuario->name} fue bloqueado.");
    }

    public function desbloquear(User $usuario): RedirectResponse
    {
        $usuario->forceFill([
            'bloqueado_at' => null,
            'bloqueado_por' => null,
        ])->save();

        return back()->with('exito', "{$usuario->name} fue desbloqueado.");
    }
}
