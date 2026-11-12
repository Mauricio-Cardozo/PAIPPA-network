<x-layouts.publico titulo="Mapa de puntos de retiro">
    <x-slot name="head">
        @vite('resources/js/mapa.js')
    </x-slot>

    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
        <h1 class="text-2xl font-bold text-verde-800">Dónde retirar</h1>
        <p class="mb-4 text-sm text-gray-600">Tocá un marcador para ver el horario y las ofertas de esa feria.</p>

        <div id="mapa" data-url="{{ route('mapa.puntos') }}"
             class="z-0 h-[60vh] min-h-[320px] w-full rounded-lg shadow-sm ring-1 ring-gray-200"
             role="region" aria-label="Mapa de los puntos de retiro"></div>

        <h2 class="mb-2 mt-6 font-semibold text-gray-800">Todos los puntos</h2>
        <ul id="lista-puntos" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3"></ul>
    </div>
</x-layouts.publico>
