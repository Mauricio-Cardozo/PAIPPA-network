<x-layouts.admin titulo="Estadísticas">
    <x-slot name="header">
        <h1 class="text-xl font-bold text-gray-800">Estadísticas de uso</h1>
    </x-slot>

    <form method="GET" class="mb-6 flex flex-wrap items-end gap-3">
        <div>
            <label for="desde" class="block text-sm font-medium text-gray-700">Desde</label>
            <input id="desde" type="date" name="desde" value="{{ $desde->toDateString() }}" class="mt-1 rounded-md border-gray-300 shadow-sm focus:border-verde-500 focus:ring-verde-500">
        </div>
        <div>
            <label for="hasta" class="block text-sm font-medium text-gray-700">Hasta</label>
            <input id="hasta" type="date" name="hasta" value="{{ $hasta->toDateString() }}" class="mt-1 rounded-md border-gray-300 shadow-sm focus:border-verde-500 focus:ring-verde-500">
        </div>
        <x-boton>Ver</x-boton>
        @error('hasta') <p class="w-full text-sm text-red-600">{{ $message }}</p> @enderror
    </form>

    <div class="mb-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
        @foreach ($indicadores as $nombre => $valor)
            <x-tarjeta class="!p-4">
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ $nombre }}</p>
                <p class="mt-1 text-3xl font-bold text-verde-800">{{ $valor }}</p>
            </x-tarjeta>
        @endforeach
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <x-admin.barras titulo="Productos más consultados"
            :filas="$productosMasConsultados->map(fn ($fila) => [$fila->producto?->nombre ?? 'Producto eliminado', $fila->total])->all()" />

        <x-admin.barras titulo="Clics en WhatsApp por feriante"
            :filas="$clicsPorFeriante->map(fn ($fila) => [$fila->feriante?->nombre ?? 'Feriante eliminado', $fila->total])->all()" />
    </div>

    <p class="mt-4 text-xs text-gray-500">
        Un clic en WhatsApp registra el interés de un revendedor, no una compra concretada.
        Los clics se borran a los 12 meses.
    </p>
</x-layouts.admin>
