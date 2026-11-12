<x-layouts.publico titulo="Catálogo">
    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
        <div class="mb-4">
            <h1 class="text-2xl font-bold text-verde-800">Qué hay en las ferias</h1>
            <p class="text-sm text-gray-600">Oferta de los feriantes del PAIPPA en Formosa Capital. Los precios son estimados.</p>
        </div>

        {{-- Filtros: formulario GET. En el celular se pliegan con Alpine. --}}
        <div x-data="{ abierto: {{ $filtros ? 'true' : 'false' }} }" class="mb-6 rounded-lg bg-white p-4 shadow-sm ring-1 ring-gray-200">
            <button type="button" class="flex w-full items-center justify-between text-sm font-semibold text-gray-700 sm:hidden" @click="abierto = ! abierto" :aria-expanded="abierto">
                Buscar y filtrar
                <span x-text="abierto ? '−' : '+'"></span>
            </button>

            <form method="GET" action="{{ route('catalogo') }}" class="mt-3 grid gap-3 sm:mt-0 sm:grid-cols-2 lg:grid-cols-5 lg:items-end" :class="abierto ? 'grid' : 'hidden sm:grid'" role="search">
                <div class="lg:col-span-2">
                    <label for="q" class="block text-sm font-medium text-gray-700">Producto</label>
                    <input id="q" type="search" name="q" value="{{ $filtros['q'] ?? '' }}" placeholder="Ej.: tomate, mandioca"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-verde-500 focus:ring-verde-500">
                </div>
                <div>
                    <label for="categoria" class="block text-sm font-medium text-gray-700">Categoría</label>
                    <select id="categoria" name="categoria" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-verde-500 focus:ring-verde-500">
                        <option value="">Todas</option>
                        @foreach ($categorias as $categoria)
                            <option value="{{ $categoria->id }}" @selected(($filtros['categoria'] ?? null) === $categoria->id)>{{ $categoria->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="feria" class="block text-sm font-medium text-gray-700">Feria</label>
                    <select id="feria" name="feria" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-verde-500 focus:ring-verde-500">
                        <option value="">Todas</option>
                        @foreach ($ferias as $feria)
                            <option value="{{ $feria->id }}" @selected(($filtros['feria'] ?? null) === $feria->id)>{{ $feria->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="precio_max" class="block text-sm font-medium text-gray-700">Precio máximo ($)</label>
                    <input id="precio_max" type="number" name="precio_max" min="0" step="1" value="{{ isset($filtros['precio_max']) ? (int) $filtros['precio_max'] : '' }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-verde-500 focus:ring-verde-500">
                </div>
                <div class="flex gap-2 sm:col-span-2 lg:col-span-5">
                    <x-boton>Buscar</x-boton>
                    @if ($filtros)
                        <x-boton variante="secundario" :href="route('catalogo')">Limpiar</x-boton>
                    @endif
                </div>
            </form>
        </div>

        @if ($ofertas->isEmpty())
            <div class="rounded-lg border-2 border-dashed border-gray-300 bg-white p-10 text-center">
                <p class="font-semibold text-gray-800">No encontramos ofertas{{ $filtros ? ' con esos filtros' : ' por ahora' }}.</p>
                @if ($filtros)
                    <p class="mt-1 text-sm text-gray-600">Probá con otra búsqueda o <a href="{{ route('catalogo') }}" class="text-verde-700 underline">mirá todo el catálogo</a>.</p>
                @endif
            </div>
        @else
            <p class="mb-3 text-sm text-gray-600">{{ $ofertas->total() }} {{ $ofertas->total() === 1 ? 'oferta' : 'ofertas' }}</p>

            {{-- 1 columna en 320 px, 2 en 768 px, 3 o 4 en escritorio. --}}
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @foreach ($ofertas as $oferta)
                    <x-tarjeta-oferta :oferta="$oferta" />
                @endforeach
            </div>

            <div class="mt-6">{{ $ofertas->links() }}</div>
        @endif
    </div>
</x-layouts.publico>
