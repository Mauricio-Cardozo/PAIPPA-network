<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-paippa-green">Ofertas</h2>
            <a href="{{ route('admin.ofertas.create') }}"
               class="rounded-md bg-paippa-green px-4 py-2 text-sm font-semibold text-white hover:opacity-90">
                + Nueva oferta
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">

            @if (session('exito'))
                <div class="mb-4 rounded-md bg-green-50 p-3 text-sm text-green-800">{{ session('exito') }}</div>
            @endif
            @if (session('error'))
                <div class="mb-4 rounded-md bg-red-50 p-3 text-sm text-red-800">{{ session('error') }}</div>
            @endif
            @if ($errors->any())
                <div class="mb-4 rounded-md bg-red-50 p-3 text-sm text-red-800">{{ $errors->first() }}</div>
            @endif

            {{-- Buscador por producto y filtro por feriante --}}
            <form method="GET" class="mb-4 flex flex-col gap-2 sm:flex-row">
                <x-text-input name="buscar" :value="request('buscar')" placeholder="Buscar por producto" class="w-full" />
                <select name="feriante" aria-label="Feriante" class="rounded-md border-gray-300 shadow-sm">
                    <option value="">Todos los feriantes</option>
                    @foreach ($feriantes as $feriante)
                        <option value="{{ $feriante->id }}" @selected(request('feriante') == $feriante->id)>{{ $feriante->nombre }}</option>
                    @endforeach
                </select>
                <x-secondary-button type="submit">Buscar</x-secondary-button>
            </form>

            @if ($ofertas->isEmpty())
                <p class="rounded-md bg-white p-6 text-center text-gray-500 shadow-sm">Todavía no hay ofertas.</p>
            @else
                <div class="overflow-x-auto rounded-md bg-white shadow-sm">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 text-left text-gray-600">
                            <tr>
                                <th class="px-4 py-3">Producto</th>
                                <th class="px-4 py-3">Feriante</th>
                                <th class="px-4 py-3">Precio</th>
                                <th class="px-4 py-3">Cantidad y vigencia</th>
                                <th class="px-4 py-3">Estado</th>
                                <th class="px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @foreach ($ofertas as $oferta)
                                @php($unidad = $oferta->producto?->unidad?->value)
                                <tr>
                                    <td class="px-4 py-3 font-medium">
                                        {{ $oferta->producto?->nombre ?? '—' }}
                                        @if ($oferta->promocion)
                                            <span class="ml-1 rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-800">Promoción</span>
                                        @endif
                                        <span class="block text-xs font-normal text-gray-500">Actualizada {{ $oferta->updated_at->diffForHumans() }}</span>
                                    </td>
                                    <td class="px-4 py-3">{{ $oferta->feriante?->nombre ?? '—' }}</td>
                                    <td class="whitespace-nowrap px-4 py-3">$ {{ number_format((float) $oferta->precio, 2, ',', '.') }} <span class="text-xs text-gray-500">por {{ $unidad }}</span></td>
                                    <td class="px-4 py-3">
                                        @if ($oferta->cerrada_at)
                                            {{-- PAI-150: una oferta cerrada ya no se actualiza; se muestra lo que se vendió. --}}
                                            <span class="text-xs text-gray-600">
                                                Cerrada el {{ $oferta->cerrada_at->format('d/m/Y') }}
                                                @if ($oferta->cantidad_vendida !== null)
                                                    · vendido {{ number_format((float) $oferta->cantidad_vendida, 2, ',', '.') }} {{ $unidad }}
                                                @endif
                                            </span>
                                        @else
                                        {{-- PAI-58: acción rápida para la cantidad y la fecha «hasta» --}}
                                        <form method="POST" action="{{ route('admin.ofertas.stock', $oferta) }}" class="flex flex-wrap items-center gap-1">
                                            @csrf
                                            @method('PATCH')
                                            <input type="text" inputmode="decimal" name="cantidad_disponible" value="{{ (float) $oferta->cantidad_disponible }}"
                                                   class="w-20 rounded-md border-gray-300 py-1 text-sm" aria-label="Cantidad disponible">
                                            <input type="date" name="vigente_hasta" value="{{ $oferta->vigente_hasta->toDateString() }}"
                                                   class="rounded-md border-gray-300 py-1 text-sm" aria-label="Vigente hasta">
                                            <button class="rounded-md bg-paippa-green px-2 py-1 text-xs font-semibold text-white hover:opacity-90">Actualizar</button>
                                        </form>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <span @class([
                                            'whitespace-nowrap rounded-full px-2 py-0.5 text-xs font-semibold',
                                            'bg-green-100 text-green-800' => $oferta->estado === \App\Enums\EstadoOferta::Publicada,
                                            'bg-gray-100 text-gray-700' => $oferta->estado !== \App\Enums\EstadoOferta::Publicada,
                                        ])>{{ $oferta->estado->etiqueta() }}</span>
                                    </td>
                                    <td class="space-x-3 whitespace-nowrap px-4 py-3 text-right" x-data="{ cerrando: false }">
                                        @unless ($oferta->cerrada_at)
                                            <a href="{{ route('admin.ofertas.edit', $oferta) }}" class="font-medium text-paippa-green hover:underline">Editar</a>
                                            {{-- PAI-150: «Cerrar» abre un formulario chico con la cantidad vendida (opcional). --}}
                                            <button type="button" x-on:click="cerrando = ! cerrando" class="font-medium text-gray-700 hover:underline">Cerrar</button>
                                        @endunless
                                        <form method="POST" action="{{ route('admin.ofertas.destroy', $oferta) }}" class="inline"
                                              onsubmit="return confirm('¿Dar de baja esta oferta?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="font-medium text-red-700 hover:underline">Dar de baja</button>
                                        </form>

                                        @unless ($oferta->cerrada_at)
                                            <form method="POST" action="{{ route('admin.ofertas.cerrar', $oferta) }}" x-show="cerrando" style="display: none"
                                                  class="mt-2 flex flex-wrap items-center justify-end gap-1"
                                                  onsubmit="return confirm('¿Cerrar esta oferta? Deja de mostrarse en el catálogo.')">
                                                @csrf
                                                @method('PATCH')
                                                <input type="text" inputmode="decimal" name="cantidad_vendida" placeholder="Vendido (opcional)"
                                                       class="w-36 rounded-md border-gray-300 py-1 text-sm" aria-label="Cantidad vendida">
                                                <button class="rounded-md bg-gray-700 px-2 py-1 text-xs font-semibold text-white hover:opacity-90">Confirmar cierre</button>
                                            </form>
                                        @endunless
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">{{ $ofertas->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
