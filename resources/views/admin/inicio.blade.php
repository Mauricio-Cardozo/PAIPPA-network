<x-layouts.admin titulo="Inicio">
    <x-slot name="header">
        <h1 class="text-xl font-bold text-gray-800">Hola, {{ auth()->user()->name }}</h1>
        <x-boton :href="route('admin.ofertas.create')">+ Cargar oferta</x-boton>
    </x-slot>

    <div class="mb-6 grid gap-3 sm:grid-cols-2">
        <a href="{{ route('admin.feriantes.index', ['estado' => 'pendientes']) }}" class="block rounded-lg bg-white p-4 shadow-sm ring-1 ring-gray-200 hover:ring-verde-400">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Feriantes pendientes de validar</p>
            <p class="mt-1 text-3xl font-bold {{ $pendientes > 0 ? 'text-zapallo-600' : 'text-verde-800' }}">{{ $pendientes }}</p>
        </a>
        <a href="{{ route('catalogo') }}" class="block rounded-lg bg-white p-4 shadow-sm ring-1 ring-gray-200 hover:ring-verde-400">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Ofertas publicadas hoy</p>
            <p class="mt-1 text-3xl font-bold text-verde-800">{{ $publicadas }}</p>
        </a>
    </div>

    <x-tarjeta>
        <h2 class="font-semibold text-gray-800">Ofertas que vencen en los próximos 2 días</h2>
        <p class="mb-3 text-sm text-gray-600">Pedile al feriante los datos nuevos y actualizalas desde «Ofertas».</p>

        @forelse ($porVencer as $oferta)
            <div class="flex justify-between border-t border-gray-100 py-2 text-sm">
                <span>{{ $oferta->producto->nombre }} · {{ $oferta->feriante->nombre }}</span>
                <span class="text-gray-600">hasta el {{ $oferta->vigente_hasta->format('d/m') }}</span>
            </div>
        @empty
            <p class="text-sm text-gray-500">No hay ofertas por vencer.</p>
        @endforelse
    </x-tarjeta>
</x-layouts.admin>
