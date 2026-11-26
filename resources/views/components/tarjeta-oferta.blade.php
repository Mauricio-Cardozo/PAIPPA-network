@props(['oferta'])

@php
    $unidad = $oferta->producto->unidad?->value ?? 'unidad';

    $abreviatura = match ($unidad) {
        'kilo' => 'kg',
        'caja' => 'cj',
        'docena' => 'doc',
        'unidad' => 'un',
        'bolsa' => 'bolsa',
        'atado' => 'atado',
        'maple' => 'maple',
        'frasco' => 'frasco',
        default => $unidad,
    };

    $cantidad = (float) $oferta->cantidad_disponible;

    $cantidadFormateada = $cantidad == floor($cantidad)
        ? number_format($cantidad, 0, ',', '.')
        : number_format($cantidad, 2, ',', '.');

    $unidadCantidad = match ($unidad) {
        'caja' => $cantidad == 1 ? 'caja' : 'cajas',
        'bolsa' => $cantidad == 1 ? 'bolsa' : 'bolsas',
        'docena' => $cantidad == 1 ? 'docena' : 'docenas',
        'unidad' => $cantidad == 1 ? 'unidad' : 'unidades',
        'atado' => $cantidad == 1 ? 'atado' : 'atados',
        'maple' => $cantidad == 1 ? 'maple' : 'maples',
        'frasco' => $cantidad == 1 ? 'frasco' : 'frascos',
        'kilo' => 'kg',
        default => $unidad,
    };
@endphp

<article
    class="flex h-full flex-col gap-3 rounded-2xl
           border border-gray-200 bg-white p-3 shadow-sm"
>

    {{-- Imagen provisional --}}
    <div
        class="flex h-52 items-center justify-center
               rounded-xl bg-green-50"
    >
        <div class="text-center">

            <div
                class="mx-auto flex h-16 w-16 items-center
                       justify-center rounded-full
                       bg-paippa-green text-2xl
                       font-bold text-white"
            >
                {{ mb_strtoupper(mb_substr($oferta->producto->nombre, 0, 1)) }}
            </div>

            <p class="mt-2 text-sm text-gray-500">
                {{ $oferta->producto->nombre }}
            </p>

        </div>
    </div>


    {{-- Producto --}}
    <div>

        <p class="text-xs font-medium text-paippa-green">
            {{ $oferta->producto->categoria?->nombre ?? 'Sin categoría' }}
        </p>

        <h2 class="mt-1 text-xl font-bold text-gray-900">
            {{ $oferta->producto->nombre }}
        </h2>

    </div>


    {{-- Datos --}}
    <div class="space-y-2 text-sm">

        <div class="flex justify-between gap-4">
            <span class="text-gray-500">
                Precio estimado
            </span>

            <span class="font-semibold text-gray-900">
                ${{ number_format((float) $oferta->precio, 0, ',', '.') }}/{{ $abreviatura }}
            </span>
        </div>


        <div class="flex justify-between gap-4">
            <span class="text-gray-500">
                Cantidad
            </span>

            <span class="font-semibold text-gray-900">
                {{ $cantidadFormateada }} {{ $unidadCantidad }}
            </span>
        </div>


        <div class="flex justify-between gap-4">
            <span class="text-gray-500">
                Feria
            </span>

            <span class="text-right font-semibold text-gray-900">
                {{ $oferta->feriante->feria?->nombre ?? 'Sin feria' }}
            </span>
        </div>


        <div class="flex justify-between gap-4">
            <span class="text-gray-500">
                Feriante
            </span>

            <span class="text-right font-semibold text-gray-900">
                {{ $oferta->feriante->nombre }}
            </span>
        </div>


        <div class="flex justify-between gap-4">
            <span class="text-gray-500">
                Retiro
            </span>

            <span class="max-w-[65%] text-right font-semibold text-gray-900">
                {{ $oferta->puntoRetiro->direccion }}
            </span>
        </div>

    </div>


    {{-- Última actualización --}}
    <p class="text-xs text-gray-500">
        Actualizado: {{ $oferta->updated_at->format('d/m/Y') }}
    </p>


    {{-- Acciones --}}
    <div class="mt-auto grid grid-cols-2 gap-2">

        <button
            type="button"
            disabled
            class="rounded-xl bg-paippa-green
                   px-3 py-3 text-sm font-semibold
                   text-white opacity-70"
        >
            Ver feriante
        </button>

        <button
            type="button"
            disabled
            class="rounded-xl border border-paippa-green
                   bg-white px-3 py-3 text-sm
                   font-semibold text-paippa-green
                   opacity-70"
        >
            Ubicación
        </button>

    </div>


    {{-- WhatsApp --}}
    @auth

        <button
            type="button"
            disabled
            class="w-full rounded-xl bg-paippa-green
                   px-4 py-3 text-sm font-semibold
                   text-white opacity-70"
        >
            Contactar por WhatsApp
        </button>

    @else

        <a
            href="{{ route('login') }}"
            class="block w-full rounded-xl border
                   border-paippa-green bg-white
                   px-4 py-3 text-center text-sm
                   font-semibold text-paippa-green
                   transition hover:bg-green-50"
        >
            Iniciá sesión para contactar
        </a>

    @endauth

</article>