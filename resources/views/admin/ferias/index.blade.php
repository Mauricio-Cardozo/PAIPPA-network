<x-layouts.admin titulo="Ferias">
    <x-slot name="header">
        <h1 class="text-xl font-bold text-gray-800">Ferias</h1>
        <x-boton :href="route('admin.ferias.create')">+ Nueva feria</x-boton>
    </x-slot>

    <x-admin.buscador />

    @if ($ferias->isEmpty())
        <x-admin.vacio>Todavía no hay ferias.</x-admin.vacio>
    @else
        <x-admin.tabla :columnas="['Nombre', 'Días y horario', 'Puntos', 'Feriantes']">
            @foreach ($ferias as $feria)
                <tr>
                    <td class="px-4 py-3 font-medium" data-etiqueta="Nombre">{{ $feria->nombre }}</td>
                    <td class="px-4 py-3" data-etiqueta="Días y horario">{{ $feria->dias_horario }}</td>
                    <td class="px-4 py-3" data-etiqueta="Puntos">{{ $feria->puntos_retiro_count }}</td>
                    <td class="px-4 py-3" data-etiqueta="Feriantes">{{ $feria->feriantes_count }}</td>
                    <td class="space-x-3 px-4 py-3 text-right">
                        <a href="{{ route('admin.ferias.edit', $feria) }}" class="text-sm font-medium text-verde-700 hover:text-verde-900">Editar</a>
                        <x-admin.baja :accion="route('admin.ferias.destroy', $feria)" :nombre="$feria->nombre" />
                    </td>
                </tr>
            @endforeach
        </x-admin.tabla>

        <div class="mt-4">{{ $ferias->links() }}</div>
    @endif
</x-layouts.admin>
