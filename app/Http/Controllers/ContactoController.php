<?php

namespace App\Http\Controllers;

use App\Models\ClicWhatsapp;
use App\Models\Feriante;
use App\Models\Oferta;
use App\Support\WhatsApp;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Contacto por WhatsApp. Pasa por acá (en vez de enlazar directo a wa.me)
 * para registrar el clic y para exigir que el usuario haya iniciado sesión:
 * así el teléfono del feriante no queda a la vista de cualquiera.
 */
class ContactoController extends Controller
{
    /** Contacto desde una oferta del catálogo. */
    public function oferta(Request $request, int $oferta): RedirectResponse
    {
        // Solo ofertas publicadas: si no, 404.
        $oferta = Oferta::publicadas()->with(['feriante', 'producto', 'puntoRetiro'])->findOrFail($oferta);

        $this->registrarClic($request, $oferta->feriante, $oferta);

        return redirect()->away(WhatsApp::enlace($oferta->feriante, $oferta));
    }

    /** Contacto general desde el perfil del feriante. */
    public function feriante(Request $request, int $feriante): RedirectResponse
    {
        $feriante = Feriante::validados()->findOrFail($feriante);

        $this->registrarClic($request, $feriante);

        return redirect()->away(WhatsApp::enlace($feriante));
    }

    private function registrarClic(Request $request, Feriante $feriante, ?Oferta $oferta = null): void
    {
        ClicWhatsapp::create([
            'user_id' => $request->user()->id,
            'feriante_id' => $feriante->id,
            'oferta_id' => $oferta?->id,
        ]);
    }
}
