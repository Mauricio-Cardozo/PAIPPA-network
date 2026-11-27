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

            {{-- Totales del período --}}
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <p class="text-sm text-gray-500">Ofertas publicadas</p>
                    <p class="mt-2 text-3xl font-bold text-paippa-green">{{ $totalOfertas }}</p>
                </div>
                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <p class="text-sm text-gray-500">Contactos por WhatsApp</p>
                    <p class="mt-2 text-3xl font-bold text-paippa-green">{{ $totalClics }}</p>
                </div>
                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <p class="text-sm text-gray-500">Usuarios nuevos</p>
                    <p class="mt-2 text-3xl font-bold text-paippa-green">{{ $usuariosNuevos }}</p>
                </div>
            </div>

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
