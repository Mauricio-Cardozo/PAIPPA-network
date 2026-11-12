<?php

namespace App\Http\Controllers;

use App\Models\Feriante;
use Illuminate\View\View;

/**
 * Perfil público de un feriante: sus datos, su feria, sus puntos de retiro
 * y sus ofertas publicadas.
 */
class FerianteController extends Controller
{
    public function show(int $feriante): View
    {
        // Solo feriantes validados: si no está validado, 404.
        $feriante = Feriante::validados()
            ->with([
                'feria.puntosRetiro',
                // Solo sus ofertas publicadas, con lo que necesita la tarjeta (sin N+1).
                'ofertas' => fn ($q) => $q->publicadas()
                    ->with(['producto.categoria', 'puntoRetiro'])
                    ->latest('updated_at'),
            ])
            ->findOrFail($feriante);

        // La tarjeta usa $oferta->feriante: se lo pasamos sin otra consulta.
        $feriante->ofertas->each->setRelation('feriante', $feriante);

        return view('feriantes.show', compact('feriante'));
    }
}
