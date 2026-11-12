<x-layouts.admin titulo="Ofertas">
    <x-slot name="header">
        <h1 class="text-xl font-bold text-gray-800">Ofertas</h1>
        <x-boton :href="route('admin.ofertas.create')">+ Nueva oferta</x-boton>
    </x-slot>

    <x-admin.buscador placeholder="Buscar por producto">
        <select name="feriante" class="rounded-md border-gray-300 shadow-sm focus:border-verde-500 focus:ring-verde-500" aria-label="Feriante">
            <option value="">Todos los feriantes</option>
            @foreach ($feriantes as $feriante)
                <option value="{{ $feriante->id }}" @selected(request('feriante') == $feriante->id)>{{ $feriante->nombre }}</option>
            @endforeach
        </select>
    </x-admin.buscador>

    @if ($ofertas->isEmpty())
        <x-admin.vacio>Todavía no hay ofertas.</x-admin.vacio>
    @else
        <x-admin.tabla :columnas="['Producto', 'Feriante', 'Precio por caja', 'Stock y vigencia', 'Estado']">
            @foreach ($ofertas as $oferta)
                <tr>
                    <td class="px-4 py-3 font-medium" data-etiqueta="Producto">
                        <div>
                            {{ $oferta->producto?->nombre ?? '—' }}
                            <span class="block text-xs font-normal text-gray-500">Actualizado {{ $oferta->updated_at->diffForHumans() }}</span>
                        </div>
                    </td>
                    <td class="px-4 py-3" data-etiqueta="Feriante">{{ $oferta->feriante?->nombre ?? '—' }}</td>
                    <td class="px-4 py-3" data-etiqueta="Precio por caja">$ {{ number_format($oferta->precio_caja, 2, ',', '.') }}</td>
                    <td class="px-4 py-3" data-etiqueta="Stock y vigencia">
                        {{-- Acción rápida: cajas y fecha «hasta» sin abrir el formulario. --}}
                        <form method="POST" action="{{ route('admin.ofertas.stock', $oferta) }}" class="flex flex-wrap items-center justify-end gap-1 md:justify-start">
                            @csrf
                            @method('PATCH')
                            <input type="number" name="cajas_disponibles" min="0" value="{{ $oferta->cajas_disponibles }}"
                                   class="w-20 rounded border-gray-300 py-1 text-sm" aria-label="Cajas disponibles">
                            <input type="date" name="vigente_hasta" value="{{ $oferta->vigente_hasta->toDateString() }}"
                                   class="rounded border-gray-300 py-1 text-sm" aria-label="Vigente hasta">
                            <button type="submit" class="rounded bg-verde-600 px-2 py-1 text-xs font-semibold text-white hover:bg-verde-700">Actualizar</button>
                        </form>
                    </td>
                    <td class="px-4 py-3" data-etiqueta="Estado">
                        <span class="whitespace-nowrap rounded-full px-2 py-0.5 text-xs font-semibold {{ $oferta->estado->color() }}">{{ $oferta->estado->etiqueta() }}</span>
                    </td>
                    <td class="space-x-3 whitespace-nowrap px-4 py-3 text-right">
                        <a href="{{ route('admin.ofertas.edit', $oferta) }}" class="text-sm font-medium text-verde-700 hover:text-verde-900">Editar</a>
                        <x-admin.baja :accion="route('admin.ofertas.destroy', $oferta)" :nombre="($oferta->producto?->nombre ?? 'oferta').' de '.($oferta->feriante?->nombre ?? '')" />
                    </td>
                </tr>
            @endforeach
        </x-admin.tabla>

        @if ($errors->any())
            <p class="mt-3 text-sm text-red-600">{{ $errors->first() }}</p>
        @endif

        <div class="mt-4">{{ $ofertas->links() }}</div>
    @endif
</x-layouts.admin>
