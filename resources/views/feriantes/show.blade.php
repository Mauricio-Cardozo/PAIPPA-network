<x-layouts.publico :titulo="$feriante->nombre">
    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
        <a href="{{ route('catalogo') }}" class="text-sm text-verde-700 hover:underline">← Volver al catálogo</a>

        <div class="mt-3 grid gap-6 lg:grid-cols-3">
            <x-tarjeta class="lg:col-span-1">
                <p class="text-xs font-medium uppercase tracking-wide text-verde-700">Feriante validado por el PAIPPA</p>
                <h1 class="mt-1 text-2xl font-bold text-gray-900">{{ $feriante->nombre }}</h1>
                <p class="text-gray-600">{{ $feriante->feria->nombre }} · {{ $feriante->feria->dias_horario }}</p>

                @if ($feriante->descripcion)
                    <p class="mt-3 text-sm text-gray-700">{{ $feriante->descripcion }}</p>
                @endif

                <div class="mt-4">
                    <x-boton-whatsapp :feriante="$feriante" />
                </div>

                {{-- HU12: mensaje interno, solo si el feriante tiene cuenta y el usuario es revendedor. --}}
                @if ($feriante->tieneCuenta() && auth()->user()?->esRevendedor())
                    <form method="POST" action="{{ route('mensajes.iniciar', $feriante) }}" class="mt-4">
                        @csrf
                        <x-campo nombre="cuerpo" etiqueta="O escribile por acá" tipo="textarea" required maxlength="1000" />
                        <x-boton variante="secundario" class="w-full">Enviar mensaje</x-boton>
                    </form>
                @endif

                <h2 class="mt-6 font-semibold text-gray-800">Puntos de retiro</h2>
                <ul class="mt-2 space-y-2 text-sm text-gray-700">
                    @forelse ($feriante->feria->puntosRetiro as $punto)
                        <li class="rounded-md bg-gray-50 p-2">
                            <p class="font-medium">📍 {{ $punto->nombre }}</p>
                            <p>{{ $punto->direccion }}</p>
                            <p class="text-gray-500">🕒 {{ $punto->horario }}</p>
                        </li>
                    @empty
                        <li class="text-gray-500">Sin puntos de retiro cargados.</li>
                    @endforelse
                </ul>
                @if (Route::has('mapa'))
                    <a href="{{ route('mapa') }}" class="mt-3 inline-block text-sm text-verde-700 hover:underline">Ver en el mapa →</a>
                @endif
            </x-tarjeta>

            <section class="lg:col-span-2">
                <h2 class="mb-3 text-lg font-semibold text-gray-800">Ofertas de esta semana</h2>

                @if ($feriante->ofertas->isEmpty())
                    <div class="rounded-lg border-2 border-dashed border-gray-300 bg-white p-8 text-center text-gray-600">
                        Este feriante no tiene ofertas publicadas por ahora.
                    </div>
                @else
                    <div class="grid gap-4 sm:grid-cols-2">
                        @foreach ($feriante->ofertas as $oferta)
                            <x-tarjeta-oferta :oferta="$oferta" :mostrar-feriante="false" />
                        @endforeach
                    </div>
                @endif
            </section>
        </div>
    </div>
</x-layouts.publico>
