<?php

namespace App\Http\Controllers;

use App\Http\Requests\MensajeRequest;
use App\Models\Conversacion;
use App\Models\Feriante;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * Mensajería interna (HU12, opcional).
 * - Solo un revendedor inicia una conversación, con un feriante que tenga cuenta.
 * - Solo los participantes la leen y la responden (ConversacionPolicy).
 * - Cada usuario envía hasta 10 mensajes por hora (limitador 'mensajes').
 */
class MensajeController extends Controller
{
    /** Bandeja: las conversaciones del usuario, con la cantidad de no leídos. */
    public function index(Request $request): View
    {
        $user = $request->user();

        $conversaciones = Conversacion::de($user)
            ->with(['feriante', 'revendedor', 'ultimoMensaje'])
            ->withCount(['mensajes as no_leidos' => fn ($q) => $q->whereNull('leido_at')->where('user_id', '!=', $user->id)])
            ->latest('updated_at')
            ->paginate(20);

        return view('mensajes.index', compact('conversaciones'));
    }

    /** Una conversación. Al abrirla se marcan como leídos los mensajes recibidos. */
    public function show(Request $request, Conversacion $conversacion): View
    {
        Gate::authorize('view', $conversacion);

        $conversacion->mensajes()
            ->whereNull('leido_at')
            ->where('user_id', '!=', $request->user()->id)
            ->update(['leido_at' => now()]);

        $conversacion->load(['feriante', 'revendedor', 'mensajes' => fn ($q) => $q->oldest()->with('remitente:id,name')]);

        return view('mensajes.show', compact('conversacion'));
    }

    /** El revendedor escribe a un feriante (crea la conversación si no existe). */
    public function iniciar(MensajeRequest $request, int $feriante): RedirectResponse
    {
        $user = $request->user();

        if (! $user->esRevendedor()) {
            abort(403, 'Solo un revendedor puede iniciar una conversación.');
        }

        $feriante = Feriante::validados()->whereNotNull('user_id')->findOrFail($feriante);

        $conversacion = Conversacion::firstOrCreate([
            'revendedor_id' => $user->id,
            'feriante_id' => $feriante->id,
        ]);

        $this->guardarMensaje($conversacion, $request);

        return redirect()->route('mensajes.show', $conversacion)->with('exito', 'Mensaje enviado.');
    }

    /** Responder dentro de una conversación existente. */
    public function responder(MensajeRequest $request, Conversacion $conversacion): RedirectResponse
    {
        Gate::authorize('responder', $conversacion);

        $this->guardarMensaje($conversacion, $request);

        return redirect()->route('mensajes.show', $conversacion);
    }

    private function guardarMensaje(Conversacion $conversacion, MensajeRequest $request): void
    {
        $conversacion->mensajes()->create([
            'user_id' => $request->user()->id,
            'cuerpo' => $request->validated('cuerpo'),
        ]);

        // Para ordenar la bandeja por la última actividad.
        $conversacion->touch();
    }
}
