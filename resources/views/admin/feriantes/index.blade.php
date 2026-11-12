<x-layouts.admin titulo="Feriantes">
    <x-slot name="header">
        <h1 class="text-xl font-bold text-gray-800">Feriantes</h1>
        <x-boton :href="route('admin.feriantes.create')">+ Nuevo feriante</x-boton>
    </x-slot>

    <x-admin.buscador>
        <select name="estado" class="rounded-md border-gray-300 shadow-sm focus:border-verde-500 focus:ring-verde-500" aria-label="Estado">
            <option value="">Todos</option>
            <option value="pendientes" @selected(request('estado') === 'pendientes')>Pendientes de validar</option>
        </select>
    </x-admin.buscador>

    @if ($feriantes->isEmpty())
        <x-admin.vacio>No hay feriantes para mostrar.</x-admin.vacio>
    @else
        <x-admin.tabla :columnas="['Nombre', 'Feria', 'WhatsApp', 'Ofertas', 'Estado']">
            @foreach ($feriantes as $feriante)
                <tr>
                    <td class="px-4 py-3 font-medium" data-etiqueta="Nombre">{{ $feriante->nombre }}</td>
                    <td class="px-4 py-3" data-etiqueta="Feria">{{ $feriante->feria?->nombre ?? '—' }}</td>
                    <td class="px-4 py-3" data-etiqueta="WhatsApp">{{ $feriante->telefono_whatsapp }}</td>
                    <td class="px-4 py-3" data-etiqueta="Ofertas">{{ $feriante->ofertas_count }}</td>
                    <td class="px-4 py-3" data-etiqueta="Estado">
                        @if ($feriante->estaValidado())
                            <span class="rounded-full bg-verde-100 px-2 py-0.5 text-xs font-semibold text-verde-800">Validado</span>
                        @else
                            <span class="rounded-full bg-zapallo-100 px-2 py-0.5 text-xs font-semibold text-zapallo-700">Pendiente</span>
                        @endif
                    </td>
                    <td class="space-x-3 whitespace-nowrap px-4 py-3 text-right">
                        <form method="POST" class="inline"
                              action="{{ $feriante->estaValidado() ? route('admin.feriantes.invalidar', $feriante) : route('admin.feriantes.validar', $feriante) }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="text-sm font-medium {{ $feriante->estaValidado() ? 'text-gray-600 hover:text-gray-900' : 'text-verde-700 hover:text-verde-900' }}">
                                {{ $feriante->estaValidado() ? 'Invalidar' : 'Validar' }}
                            </button>
                        </form>
                        @unless ($feriante->tieneCuenta())
                            <a href="{{ route('admin.feriantes.cuenta', $feriante) }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">Crear cuenta</a>
                        @endunless
                        <a href="{{ route('admin.feriantes.edit', $feriante) }}" class="text-sm font-medium text-verde-700 hover:text-verde-900">Editar</a>
                        <x-admin.baja :accion="route('admin.feriantes.destroy', $feriante)" :nombre="$feriante->nombre" />
                    </td>
                </tr>
            @endforeach
        </x-admin.tabla>

        <div class="mt-4">{{ $feriantes->links() }}</div>
    @endif
</x-layouts.admin>
