{{--
    Tarjeta de una oferta del catálogo y del perfil del feriante.
    Espera la oferta con producto.categoria, feriante y puntoRetiro cargados.
--}}
@props(['oferta', 'mostrarFeriante' => true])

<article class="flex flex-col rounded-lg bg-white p-4 shadow-sm ring-1 ring-gray-200">
    <div class="flex items-start justify-between gap-2">
        <div>
            <p class="text-xs font-medium uppercase tracking-wide text-verde-700">{{ $oferta->producto->categoria?->nombre }}</p>
            <h2 class="text-lg font-semibold text-gray-900">{{ $oferta->producto->nombre }}</h2>
        </div>
        <span class="whitespace-nowrap rounded-full bg-verde-50 px-2 py-0.5 text-xs font-medium text-verde-800">
            {{ $oferta->cajas_disponibles }} {{ $oferta->producto->unidad }}{{ $oferta->cajas_disponibles === 1 ? '' : 's' }}
        </span>
    </div>

    <p class="mt-2 text-2xl font-bold text-gray-900">
        $ {{ number_format((float) $oferta->precio_caja, 2, ',', '.') }}
        <span class="text-sm font-normal text-gray-500">por {{ $oferta->producto->unidad }}</span>
    </p>
    <p class="text-xs text-zapallo-700">Precio estimado</p>

    <dl class="mt-3 space-y-1 text-sm text-gray-700">
        @if ($mostrarFeriante)
            <div class="flex gap-1">
                <dt class="sr-only">Feriante</dt>
                <dd>
                    👤
                    @if (Route::has('feriantes.show'))
                        <a href="{{ route('feriantes.show', $oferta->feriante) }}" class="font-medium text-verde-700 hover:underline">{{ $oferta->feriante->nombre }}</a>
                    @else
                        {{ $oferta->feriante->nombre }}
                    @endif
                    · {{ $oferta->feriante->feria?->nombre }}
                </dd>
            </div>
        @endif
        <div>
            <dt class="sr-only">Punto de retiro</dt>
            <dd>📍 {{ $oferta->puntoRetiro->nombre }} — {{ $oferta->puntoRetiro->direccion }}</dd>
        </div>
        <div>
            <dt class="sr-only">Horario de retiro</dt>
            <dd>🕒 {{ $oferta->puntoRetiro->horario }}</dd>
        </div>
    </dl>

    <p class="mt-3 text-xs text-gray-500">
        Actualizado {{ $oferta->updated_at->diffForHumans() }} · Vigente hasta el {{ $oferta->vigente_hasta->format('d/m') }}
    </p>

    <div class="mt-auto pt-4">
        <x-boton-whatsapp :oferta="$oferta" />
    </div>
</article>
