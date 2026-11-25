<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-paippa-green">Feriantes</h2>
            <a href="{{ route('admin.feriantes.create') }}"
               class="rounded-md bg-paippa-green px-4 py-2 text-sm font-semibold text-white hover:opacity-90">
                + Nuevo feriante
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">

            @if (session('exito'))
                <div class="mb-4 rounded-md bg-green-50 p-3 text-sm text-green-800">{{ session('exito') }}</div>
            @endif
            @if (session('error'))
                <div class="mb-4 rounded-md bg-red-50 p-3 text-sm text-red-800">{{ session('error') }}</div>
            @endif

            {{-- Buscador por nombre y filtro de los que faltan validar --}}
            <form method="GET" class="mb-4 flex flex-col gap-2 sm:flex-row">
                <x-text-input name="buscar" :value="request('buscar')" placeholder="Buscar por nombre" class="w-full" />
                <select name="estado" aria-label="Estado" class="rounded-md border-gray-300 shadow-sm">
                    <option value="">Todos</option>
                    <option value="pendientes" @selected(request('estado') === 'pendientes')>Pendientes de validar</option>
                </select>
                <x-secondary-button type="submit">Buscar</x-secondary-button>
            </form>

            @if ($feriantes->isEmpty())
                <p class="rounded-md bg-white p-6 text-center text-gray-500 shadow-sm">No hay feriantes para mostrar.</p>
            @else
                <div class="overflow-x-auto rounded-md bg-white shadow-sm">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 text-left text-gray-600">
                            <tr>
                                <th class="px-4 py-3">Nombre</th>
                                <th class="px-4 py-3">Feria</th>
                                <th class="px-4 py-3">Procedencia</th>
                                <th class="px-4 py-3">WhatsApp</th>
                                <th class="px-4 py-3">Ofertas</th>
                                <th class="px-4 py-3">Estado</th>
                                <th class="px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @foreach ($feriantes as $feriante)
                                <tr>
                                    <td class="px-4 py-3 font-medium">{{ $feriante->nombre }}</td>
                                    <td class="px-4 py-3">{{ $feriante->feria?->nombre ?? '—' }}</td>
                                    <td class="px-4 py-3">{{ $feriante->procedencia ?? '—' }}</td>
                                    <td class="px-4 py-3">
                                        {{ $feriante->telefono_whatsapp }}
                                        @unless ($feriante->autoriza_whatsapp)
                                            <span class="block text-xs text-gray-500">No autoriza mostrarlo</span>
                                        @endunless
                                    </td>
                                    <td class="px-4 py-3">{{ $feriante->ofertas_count }}</td>
                                    <td class="px-4 py-3">
                                        @if ($feriante->estaValidado())
                                            <span class="rounded-full bg-green-100 px-2 py-0.5 text-xs font-semibold text-green-800">Validado</span>
                                        @else
                                            <span class="rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-800">Pendiente</span>
                                        @endif
                                    </td>
                                    <td class="space-x-3 whitespace-nowrap px-4 py-3 text-right">
                                        {{-- Validar e invalidar son acciones propias, no se hacen desde el formulario --}}
                                        <form method="POST" class="inline"
                                              action="{{ $feriante->estaValidado() ? route('admin.feriantes.invalidar', $feriante) : route('admin.feriantes.validar', $feriante) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button class="font-medium {{ $feriante->estaValidado() ? 'text-gray-600' : 'text-paippa-green' }} hover:underline">
                                                {{ $feriante->estaValidado() ? 'Invalidar' : 'Validar' }}
                                            </button>
                                        </form>
                                        <a href="{{ route('admin.feriantes.edit', $feriante) }}" class="font-medium text-paippa-green hover:underline">Editar</a>
                                        <form method="POST" action="{{ route('admin.feriantes.destroy', $feriante) }}" class="inline"
                                              onsubmit="return confirm('¿Dar de baja {{ $feriante->nombre }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="font-medium text-red-700 hover:underline">Dar de baja</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">{{ $feriantes->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
