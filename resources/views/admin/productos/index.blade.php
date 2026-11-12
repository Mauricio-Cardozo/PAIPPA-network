<x-layouts.admin titulo="Productos">
    <x-slot name="header">
        <h1 class="text-xl font-bold text-gray-800">Productos</h1>
        <x-boton :href="route('admin.productos.create')">+ Nuevo producto</x-boton>
    </x-slot>

    <x-admin.buscador />

    @if ($productos->isEmpty())
        <x-admin.vacio>Todavía no hay productos.</x-admin.vacio>
    @else
        <x-admin.tabla :columnas="['Nombre', 'Categoría', 'Unidad']">
            @foreach ($productos as $producto)
                <tr>
                    <td class="px-4 py-3 font-medium" data-etiqueta="Nombre">{{ $producto->nombre }}</td>
                    <td class="px-4 py-3" data-etiqueta="Categoría">{{ $producto->categoria?->nombre ?? '—' }}</td>
                    <td class="px-4 py-3" data-etiqueta="Unidad">{{ $producto->unidad }}</td>
                    <td class="space-x-3 px-4 py-3 text-right">
                        <a href="{{ route('admin.productos.edit', $producto) }}" class="text-sm font-medium text-verde-700 hover:text-verde-900">Editar</a>
                        <x-admin.baja :accion="route('admin.productos.destroy', $producto)" :nombre="$producto->nombre" />
                    </td>
                </tr>
            @endforeach
        </x-admin.tabla>

        <div class="mt-4">{{ $productos->links() }}</div>
    @endif
</x-layouts.admin>
