<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FerianteRequest;
use App\Models\Feria;
use App\Models\Feriante;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * ABM de feriantes. Un feriante nuevo queda pendiente de validar:
 * solo se publica cuando el admin lo valida contra los datos del PAIPPA.
 */
class FerianteController extends Controller
{
    public function index(Request $request): View
    {
        $feriantes = Feriante::query()
            ->with('feria')
            ->withCount('ofertas')
            ->when($request->filled('buscar'), fn ($q) => $q->where('nombre', 'like', '%'.$request->input('buscar').'%'))
            ->when($request->input('estado') === 'pendientes', fn ($q) => $q->whereNull('validado_at'))
            ->orderBy('nombre')
            ->paginate(15)
            ->withQueryString();

        return view('admin.feriantes.index', compact('feriantes'));
    }

    public function create(): View
    {
        return view('admin.feriantes.form', [
            'feriante' => new Feriante,
            'ferias' => Feria::orderBy('nombre')->get(),
        ]);
    }

    public function store(FerianteRequest $request): RedirectResponse
    {
        // validado_at queda nulo: el feriante nace sin validar.
        Feriante::create($request->validated());

        return redirect()->route('admin.feriantes.index')->with('exito', 'Feriante creado. Queda pendiente de validar.');
    }

    public function edit(Feriante $feriante): View
    {
        return view('admin.feriantes.form', [
            'feriante' => $feriante,
            'ferias' => Feria::orderBy('nombre')->get(),
        ]);
    }

    public function update(FerianteRequest $request, Feriante $feriante): RedirectResponse
    {
        $feriante->update($request->validated());

        return redirect()->route('admin.feriantes.index')->with('exito', 'Feriante actualizado.');
    }

    /** Baja lógica: no se permite si tiene ofertas cargadas. */
    public function destroy(Feriante $feriante): RedirectResponse
    {
        if ($feriante->ofertas()->exists()) {
            return back()->with('error', 'No se puede dar de baja: el feriante tiene ofertas cargadas. Invalidalo para ocultarlo.');
        }

        $feriante->delete();

        return redirect()->route('admin.feriantes.index')->with('exito', 'Feriante dado de baja.');
    }

    /** Marca al feriante como validado: desde ahora se publican sus ofertas. */
    public function validar(Feriante $feriante): RedirectResponse
    {
        $feriante->validado_at = now();
        $feriante->save();

        return back()->with('exito', "{$feriante->nombre} fue validado.");
    }

    /** Quita la validación: sus ofertas dejan de publicarse. */
    public function invalidar(Feriante $feriante): RedirectResponse
    {
        $feriante->validado_at = null;
        $feriante->save();

        return back()->with('exito', "{$feriante->nombre} quedó sin validar.");
    }
}
