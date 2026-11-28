<?php

namespace App\Http\Controllers;

use App\Enums\OrigenConsulta;
use App\Models\ClicWhatsapp;
use App\Models\ConsultaProducto;
use App\Models\Oferta;
use App\Support\WhatsApp;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * PAI-75: el botón «Contactar por WhatsApp» pasa por acá. Se registra el clic
 * (sin el mensaje) y se redirige a WhatsApp con el mensaje armado (PAI-74).
 */
class ContactoController extends Controller
{
    public function whatsapp(Request $request, Oferta $oferta): RedirectResponse
    {
        // Solo se contacta por ofertas publicadas (vigentes, con stock y de feriantes validados).
        abort_unless(Oferta::publicadas()->whereKey($oferta->id)->exists(), 404);

        $oferta->load(['feriante', 'producto', 'puntoRetiro']);
        $enlace = WhatsApp::enlace($oferta->feriante, $oferta);

        // RN-06: si el feriante no autoriza y no hay número de Comercialización cargado.
        if ($enlace === null) {
            return back()->with('error', 'Este feriante no tiene contacto por WhatsApp. Consultá en el PAIPPA.');
        }

        ClicWhatsapp::create([
            'user_id' => $request->user()->id,
            'feriante_id' => $oferta->feriante_id,
            'oferta_id' => $oferta->id,
        ]);

        // PAI-89 (HU15): el contacto también cuenta como consulta del producto.
        ConsultaProducto::registrar([$oferta->producto_id], OrigenConsulta::Whatsapp, $request->user(), $request->session()->getId());

        return redirect()->away($enlace);
    }
}
