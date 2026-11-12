<x-layouts.admin :titulo="$oferta->exists ? 'Editar oferta' : 'Nueva oferta'">
    <x-slot name="header">
        <h1 class="text-xl font-bold text-gray-800">{{ $oferta->exists ? 'Editar oferta' : 'Nueva oferta' }}</h1>
    </x-slot>

    <x-tarjeta class="max-w-2xl">
        @if ($feriantes->isEmpty())
            <p class="text-gray-700">Primero tenés que <a class="text-verde-700 underline" href="{{ route('admin.feriantes.index') }}">validar al menos un feriante</a>.</p>
        @else
            <form method="POST" action="{{ $oferta->exists ? route('admin.ofertas.update', $oferta) : route('admin.ofertas.store') }}">
                @csrf
                @if ($oferta->exists)
                    @method('PUT')
                @endif

                <x-campo nombre="feriante_id" etiqueta="Feriante (solo validados)" tipo="select" required>
                    <option value="">Elegí un feriante</option>
                    @foreach ($feriantes as $feriante)
                        <option value="{{ $feriante->id }}" @selected(old('feriante_id', $oferta->feriante_id) == $feriante->id)>{{ $feriante->nombre }} · {{ $feriante->feria?->nombre }}</option>
                    @endforeach
                </x-campo>

                <x-campo nombre="producto_id" etiqueta="Producto" tipo="select" required>
                    <option value="">Elegí un producto</option>
                    @foreach ($productos as $producto)
                        <option value="{{ $producto->id }}" @selected(old('producto_id', $oferta->producto_id) == $producto->id)>{{ $producto->nombre }} ({{ $producto->unidad }})</option>
                    @endforeach
                </x-campo>

                {{-- Agrupados por feria: tiene que ser un punto de la feria del feriante. --}}
                <x-campo nombre="punto_retiro_id" etiqueta="Punto de retiro" tipo="select" required
                         ayuda="Tiene que ser un punto de la misma feria del feriante.">
                    <option value="">Elegí un punto de retiro</option>
                    @foreach ($ferias as $feria)
                        <optgroup label="{{ $feria->nombre }}">
                            @foreach ($feria->puntosRetiro as $punto)
                                <option value="{{ $punto->id }}" @selected(old('punto_retiro_id', $oferta->punto_retiro_id) == $punto->id)>{{ $punto->nombre }}</option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </x-campo>

                <div class="grid gap-x-4 sm:grid-cols-2">
                    <x-campo nombre="precio_caja" etiqueta="Precio estimado por unidad de venta ($)" tipo="number" step="0.01" min="0.01"
                             :valor="$oferta->precio_caja" required />
                    <x-campo nombre="cajas_disponibles" etiqueta="Cantidad disponible" tipo="number" min="0"
                             :valor="$oferta->cajas_disponibles ?? 0" required />
                    <x-campo nombre="vigente_desde" etiqueta="Vigente desde" tipo="date" :valor="$oferta->vigente_desde?->toDateString()" required />
                    <x-campo nombre="vigente_hasta" etiqueta="Vigente hasta" tipo="date" :valor="$oferta->vigente_hasta?->toDateString()" required
                             ayuda="La oferta debe actualizarse al menos una vez por semana." />
                </div>

                <div class="flex gap-3">
                    <x-boton>Guardar</x-boton>
                    <x-boton variante="secundario" :href="route('admin.ofertas.index')">Cancelar</x-boton>
                </div>
            </form>
        @endif
    </x-tarjeta>
</x-layouts.admin>
