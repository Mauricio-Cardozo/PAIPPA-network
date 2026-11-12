<x-layouts.admin titulo="Puntos de retiro">
    <x-slot name="header">
        <h1 class="text-xl font-bold text-gray-800">Puntos de retiro</h1>
        <x-boton :href="route('admin.puntos-retiro.create')">+ Nuevo punto</x-boton>
    </x-slot>

    <x-admin.buscador />

    @if ($puntos->isEmpty())
        <x-admin.vacio>Todavía no hay puntos de retiro.</x-admin.vacio>
    @else
        <x-admin.tabla :columnas="['Nombre', 'Feria', 'Dirección', 'Horario']">
            @foreach ($puntos as $punto)
                <tr>
                    <td class="px-4 py-3 font-medium" data-etiqueta="Nombre">{{ $punto->nombre }}</td>
                    <td class="px-4 py-3" data-etiqueta="Feria">{{ $punto->feria?->nombre ?? '—' }}</td>
                    <td class="px-4 py-3" data-etiqueta="Dirección">{{ $punto->direccion }}</td>
                    <td class="px-4 py-3" data-etiqueta="Horario">{{ $punto->horario }}</td>
                    <td class="space-x-3 px-4 py-3 text-right">
                        <a href="{{ route('admin.puntos-retiro.edit', $punto) }}" class="text-sm font-medium text-verde-700 hover:text-verde-900">Editar</a>
                        <x-admin.baja :accion="route('admin.puntos-retiro.destroy', $punto)" :nombre="$punto->nombre" />
                    </td>
                </tr>
            @endforeach
        </x-admin.tabla>

        <div class="mt-4">{{ $puntos->links() }}</div>
    @endif
</x-layouts.admin>
