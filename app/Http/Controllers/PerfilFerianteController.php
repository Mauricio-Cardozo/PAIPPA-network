<?php

namespace App\Http\Controllers;

use App\Models\Feriante;
use Illuminate\View\View;

/**
 * PAI-68: perfil público del feriante (HU9). Solo se muestran los feriantes
 * validados (RN-01); los demás dan 404, como si no existieran.
 */
class PerfilFerianteController extends Controller
{
    public function show(Feriante $feriante): View
    {
        abort_unless($feriante->estaValidado(), 404);

        // Eager loading: la feria con sus puntos y las ofertas con lo que usa la tarjeta.
        $feriante->load(['feria.puntosRetiro' => fn ($q) => $q->orderBy('nombre')]);

        $ofertas = $feriante->ofertas()
            ->publicadas()
            ->with(['producto.categoria', 'puntoRetiro'])
            ->latest('updated_at')
            ->get()
            // Cada oferta ya conoce a su feriante: así la tarjeta no vuelve a consultarlo.
            ->each(fn ($oferta) => $oferta->setRelation('feriante', $feriante));

        return view('feriantes.show', [
            'feriante' => $feriante,
            'ofertas' => $ofertas,
        ]);
    }
}
