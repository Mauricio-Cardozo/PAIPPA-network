{{--
    Gráfico de barras horizontal hecho solo con Tailwind (sin librerías).
    $filas: lista de [etiqueta, valor].
--}}
@props(['titulo', 'filas', 'vacio' => 'Sin datos en este período.'])

@php $maximo = max(1, collect($filas)->max(fn ($fila) => $fila[1])); @endphp

<x-tarjeta>
    <h2 class="mb-3 font-semibold text-gray-800">{{ $titulo }}</h2>

    @if (count($filas) === 0)
        <p class="text-sm text-gray-500">{{ $vacio }}</p>
    @else
        <ul class="space-y-2">
            @foreach ($filas as [$etiqueta, $valor])
                <li>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-700">{{ $etiqueta }}</span>
                        <span class="font-semibold text-gray-900">{{ $valor }}</span>
                    </div>
                    <div class="mt-1 h-2 rounded-full bg-gray-100" role="presentation">
                        <div class="h-2 rounded-full bg-verde-500" style="width: {{ round($valor / $maximo * 100) }}%"></div>
                    </div>
                </li>
            @endforeach
        </ul>
    @endif
</x-tarjeta>
