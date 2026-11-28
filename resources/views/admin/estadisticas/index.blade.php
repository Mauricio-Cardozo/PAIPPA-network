{{-- PAI-90 (HU15): indicadores de uso con filtro de fechas. Barras hechas con Tailwind, sin librería de gráficos. --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-paippa-green">Estadísticas</h2>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">

            @if ($errors->any())
                <div class="mb-4 rounded-md bg-red-50 p-3 text-sm text-red-800">{{ $errors->first() }}</div>
            @endif

            {{-- Rango de fechas --}}
            <form method="GET" class="mb-6 flex flex-col gap-3 rounded-md bg-white p-4 shadow-sm sm:flex-row sm:items-end">
                <div>
                    <x-input-label for="desde" value="Desde" />
                    <x-text-input id="desde" name="desde" type="date" :value="$desde->toDateString()" class="mt-1 w-full" />
                </div>
                <div>
                    <x-input-label for="hasta" value="Hasta" />
                    <x-text-input id="hasta" name="hasta" type="date" :value="$hasta->toDateString()" class="mt-1 w-full" />
                </div>
                <x-primary-button class="justify-center">Ver</x-primary-button>
                <a href="{{ route('admin.estadisticas') }}" class="text-sm text-gray-600 underline sm:ml-2">Últimos 30 días</a>
            </form>

            <p class="mb-4 text-sm text-gray-600">
                Del {{ $desde->format('d/m/Y') }} al {{ $hasta->format('d/m/Y') }}
            </p>

            {{-- Totales del período (RF-017) --}}
            @php($pesos = fn ($monto) => '$ '.number_format($monto, 2, ',', '.'))
            @php($cantidad = fn ($valor) => $valor === null ? '—' : number_format($valor, 2, ',', '.'))
            <div class="grid grid-cols-2 gap-4 lg:grid-cols-5">
                @foreach ([
                    'Feriantes activos' => $feriantesActivos,
                    'Ofertas publicadas' => $totalOfertas,
                    'Compradores registrados' => $compradoresNuevos,
                    'Contactos por WhatsApp' => $totalClics,
                    'Monto estimado vendido' => $pesos($montoTotal),
                ] as $rotulo => $valor)
                    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm {{ $loop->last ? 'col-span-2 lg:col-span-1' : '' }}">
                        <p class="text-sm text-gray-500">{{ $rotulo }}</p>
                        <p class="mt-2 text-2xl font-bold text-paippa-green">{{ $valor }}</p>
                    </div>
                @endforeach
            </div>

            {{-- Ofrecido y vendido (RF-017, RN-17) --}}
            <section class="mt-8 rounded-md bg-white p-5 shadow-sm">
                <h3 class="font-semibold text-gray-800">Ofrecido y vendido</h3>
                <p class="mt-1 text-xs text-gray-500">
                    Lo vendido lo informa el PAIPPA al cerrar cada oferta; si no se informó, se ve «—».
                    El monto es estimado: cantidad vendida por precio.
                </p>

                @if ($ventasPorProducto->isEmpty())
                    <p class="mt-4 text-sm text-gray-500">No hay ofertas publicadas en este período.</p>
                @else
                    {{-- Totales por unidad: kilos, unidades, docenas... --}}
                    <div class="mt-4 flex flex-wrap gap-2">
                        @foreach ($ventasPorUnidad as $fila)
                            <span class="rounded-full bg-gray-100 px-3 py-1 text-xs text-gray-700">
                                {{ $fila['unidad'] }}: ofrecido {{ $cantidad($fila['ofrecido']) }} · vendido {{ $cantidad($fila['vendido']) }}
                            </span>
                        @endforeach
                    </div>

                    <div class="mt-4 overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500">
                                <tr>
                                    <th class="px-3 py-2">Producto</th>
                                    <th class="px-3 py-2 text-right">Ofrecido</th>
                                    <th class="px-3 py-2 text-right">Vendido</th>
                                    <th class="px-3 py-2 text-right">Precio promedio</th>
                                    <th class="px-3 py-2 text-right">Monto estimado</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($ventasPorProducto as $fila)
                                    <tr>
                                        <td class="px-3 py-2">{{ $fila['producto'] }} <span class="text-xs text-gray-500">({{ $fila['unidad'] }})</span></td>
                                        <td class="whitespace-nowrap px-3 py-2 text-right">{{ $cantidad($fila['ofrecido']) }}</td>
                                        <td class="whitespace-nowrap px-3 py-2 text-right">{{ $cantidad($fila['vendido']) }}</td>
                                        <td class="whitespace-nowrap px-3 py-2 text-right">{{ $pesos($fila['precio_promedio']) }}</td>
                                        <td class="whitespace-nowrap px-3 py-2 text-right">{{ $fila['monto'] === null ? '—' : $pesos($fila['monto']) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>

            <div class="mt-8 grid grid-cols-1 gap-6 md:grid-cols-2">

                {{-- Productos más consultados --}}
                <section class="rounded-md bg-white p-5 shadow-sm">
                    <h3 class="mb-4 font-semibold text-gray-800">Productos más consultados</h3>

                    @if ($productos->isEmpty())
                        <p class="text-sm text-gray-500">No hay consultas en este período.</p>
                    @else
                        @php($maximo = $productos->max('total'))
                        <ul class="space-y-3">
                            @foreach ($productos as $fila)
                                <li>
                                    <div class="flex justify-between text-sm">
                                        <span>{{ $fila->producto?->nombre ?? 'Producto eliminado' }}</span>
                                        <span class="font-semibold">{{ $fila->total }}</span>
                                    </div>
                                    {{-- El ancho de la barra es proporcional al más consultado. --}}
                                    <div class="mt-1 h-2 rounded-full bg-gray-100">
                                        <div class="h-2 rounded-full bg-paippa-green" style="width: {{ round($fila->total * 100 / $maximo) }}%"></div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </section>

                {{-- Clics de WhatsApp por feriante --}}
                <section class="rounded-md bg-white p-5 shadow-sm">
                    <h3 class="mb-4 font-semibold text-gray-800">Contactos por WhatsApp por feriante</h3>

                    @if ($feriantes->isEmpty())
                        <p class="text-sm text-gray-500">No hay contactos en este período.</p>
                    @else
                        @php($maximo = $feriantes->max('total'))
                        <ul class="space-y-3">
                            @foreach ($feriantes as $fila)
                                <li>
                                    <div class="flex justify-between text-sm">
                                        <span>{{ $fila->feriante?->nombre ?? 'Feriante eliminado' }}</span>
                                        <span class="font-semibold">{{ $fila->total }}</span>
                                    </div>
                                    <div class="mt-1 h-2 rounded-full bg-gray-100">
                                        <div class="h-2 rounded-full bg-paippa-green" style="width: {{ round($fila->total * 100 / $maximo) }}%"></div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </section>

            </div>
        </div>
    </div>
</x-app-layout>
