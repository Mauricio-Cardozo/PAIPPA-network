<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CategoriaRequest;
use App\Models\Categoria;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * ABM de categorías. Es el MOLDE de los demás ABM del panel:
 * index (listado con búsqueda), create/store (alta), edit/update
 * (modificación) y destroy (baja lógica).
 */
class CategoriaController extends Controller
{
    /** Listado paginado con búsqueda por nombre. */
    public function index(Request $request): View
    {
        $categorias = Categoria::query()
            ->withCount('productos')
            // when() agrega el filtro solo si se escribió algo en el buscador.
            ->when($request->filled('buscar'), fn ($q) => $q->where('nombre', 'like', '%'.$request->input('buscar').'%'))
            ->orderBy('nombre')
            ->paginate(15)
            ->withQueryString();

        return view('admin.categorias.index', compact('categorias'));
    }

    public function create(): View
    {
        return view('admin.categorias.form', ['categoria' => new Categoria]);
    }

    public function store(CategoriaRequest $request): RedirectResponse
    {
        Categoria::create($request->validated());

        return redirect()->route('admin.categorias.index')->with('exito', 'Categoría creada.');
    }

    public function edit(Categoria $categoria): View
    {
        return view('admin.categorias.form', compact('categoria'));
    }

    public function update(CategoriaRequest $request, Categoria $categoria): RedirectResponse
    {
        $categoria->update($request->validated());

        return redirect()->route('admin.categorias.index')->with('exito', 'Categoría actualizada.');
    }

    /**
     * Baja lógica. Como el soft delete es un UPDATE, la clave foránea de la
     * base no lo frena: por eso se controla acá que no tenga productos activos.
     */
    public function destroy(Categoria $categoria): RedirectResponse
    {
        if ($categoria->productos()->exists()) {
            return back()->with('error', 'No se puede dar de baja: la categoría tiene productos activos.');
        }

        $categoria->delete();

        return redirect()->route('admin.categorias.index')->with('exito', 'Categoría dada de baja.');
    }
}
