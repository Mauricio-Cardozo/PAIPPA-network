<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Feriante;
use App\Models\Oferta;
use Illuminate\View\View;

/** Inicio del panel: lo que el admin tiene que atender hoy. */
class InicioController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.inicio', [
            'pendientes' => Feriante::whereNull('validado_at')->count(),
            'publicadas' => Oferta::publicadas()->count(),
            // Publicadas que vencen en los próximos 2 días: hay que pedir datos nuevos al feriante.
            'porVencer' => Oferta::publicadas()
                ->where('vigente_hasta', '<=', today()->addDays(2)->toDateString())
                ->with(['producto', 'feriante'])
                ->orderBy('vigente_hasta')
                ->limit(10)
                ->get(),
        ]);
    }
}
