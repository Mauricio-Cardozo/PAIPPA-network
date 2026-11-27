<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Feria;
use App\Models\Feriante;
use App\Models\Oferta;
use App\Models\User;
use Illuminate\View\View;

/**
 * Inicio del panel del administrador: los contadores y los accesos a cada ABM.
 * Antes era una función dentro de routes/web.php (PAI-140).
 */
class InicioController extends Controller
{
    public function index(): View
    {
        return view('admin.inicio', [
            'totalFeriantes' => Feriante::count(),
            // Los que el admin todavía tiene que validar contra los datos del PAIPPA.
            'feriantesPendientes' => Feriante::whereNull('validado_at')->count(),
            'totalFerias' => Feria::count(),
            'totalUsuarios' => User::count(),
            // Misma regla que el scope Oferta::vigentes() (PAI-56).
            'totalOfertasVigentes' => Oferta::vigentes()->count(),
        ]);
    }
}
