<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductoRequest;
use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** ABM de productos (misma estructura que CategoriaController). */
class ProductoController extends Controller
{
    public function index(Request $request): View
    {
        $productos = Producto::query()
            ->with('categoria')
            ->when($request->filled('buscar'), fn ($q) => $q->where('nombre', 'like', '%'.$request->input('buscar').'%'))
            ->orderBy('nombre')
            ->paginate(15)
            ->withQueryString();

        return view('admin.productos.index', compact('productos'));
    }

    public function create(): View
    {
        return view('admin.productos.form', [
            'producto' => new Producto(['unidad' => 'caja']),
            'categorias' => Categoria::orderBy('nombre')->get(),
        ]);
    }

    public function store(ProductoRequest $request): RedirectResponse
    {
        Producto::create($request->validated());

        return redirect()->route('admin.productos.index')->with('exito', 'Producto creado.');
    }

    public function edit(Producto $producto): View
    {
        return view('admin.productos.form', [
            'producto' => $producto,
            'categorias' => Categoria::orderBy('nombre')->get(),
        ]);
    }

    public function update(ProductoRequest $request, Producto $producto): RedirectResponse
    {
        $producto->update($request->validated());

        return redirect()->route('admin.productos.index')->with('exito', 'Producto actualizado.');
    }

    /** Baja lógica: no se permite si el producto tiene ofertas activas. */
    public function destroy(Producto $producto): RedirectResponse
    {
        if ($producto->ofertas()->exists()) {
            return back()->with('error', 'No se puede dar de baja: el producto tiene ofertas cargadas.');
        }

        $producto->delete();

        return redirect()->route('admin.productos.index')->with('exito', 'Producto dado de baja.');
    }
}
