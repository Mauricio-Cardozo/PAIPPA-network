<x-layouts.admin titulo="Categorías">
    <x-slot name="header">
        <h1 class="text-xl font-bold text-gray-800">Categorías</h1>
        <x-boton :href="route('admin.categorias.create')">+ Nueva categoría</x-boton>
    </x-slot>

    <x-admin.buscador />

    @if ($categorias->isEmpty())
        <x-admin.vacio>Todavía no hay categorías.</x-admin.vacio>
    @else
        <x-admin.tabla :columnas="['Nombre', 'Productos']">
            @foreach ($categorias as $categoria)
                <tr>
                    <td class="px-4 py-3 font-medium" data-etiqueta="Nombre">{{ $categoria->nombre }}</td>
                    <td class="px-4 py-3" data-etiqueta="Productos">{{ $categoria->productos_count }}</td>
                    <td class="space-x-3 px-4 py-3 text-right">
                        <a href="{{ route('admin.categorias.edit', $categoria) }}" class="text-sm font-medium text-verde-700 hover:text-verde-900">Editar</a>
                        <x-admin.baja :accion="route('admin.categorias.destroy', $categoria)" :nombre="$categoria->nombre" />
                    </td>
                </tr>
            @endforeach
        </x-admin.tabla>

        <div class="mt-4">{{ $categorias->links() }}</div>
    @endif
</x-layouts.admin>
