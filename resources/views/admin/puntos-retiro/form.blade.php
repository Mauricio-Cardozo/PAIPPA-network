<x-layouts.admin :titulo="$punto->exists ? 'Editar punto de retiro' : 'Nuevo punto de retiro'">
    <x-slot name="header">
        <h1 class="text-xl font-bold text-gray-800">{{ $punto->exists ? 'Editar punto de retiro' : 'Nuevo punto de retiro' }}</h1>
    </x-slot>

    <x-tarjeta class="max-w-xl">
        <form method="POST" action="{{ $punto->exists ? route('admin.puntos-retiro.update', $punto) : route('admin.puntos-retiro.store') }}">
            @csrf
            @if ($punto->exists)
                @method('PUT')
            @endif

            <x-campo nombre="feria_id" etiqueta="Feria" tipo="select" required>
                <option value="">Elegí una feria</option>
                @foreach ($ferias as $feria)
                    <option value="{{ $feria->id }}" @selected(old('feria_id', $punto->feria_id) == $feria->id)>{{ $feria->nombre }}</option>
                @endforeach
            </x-campo>

            <x-campo nombre="nombre" etiqueta="Nombre" :valor="$punto->nombre" required maxlength="100" />
            <x-campo nombre="direccion" etiqueta="Dirección" :valor="$punto->direccion" required maxlength="200" />
            <x-campo nombre="horario" etiqueta="Horario de retiro" :valor="$punto->horario" required maxlength="150" />

            <p class="mb-2 rounded bg-verde-50 p-3 text-sm text-verde-800">
                Copiá las coordenadas desde Google Maps: hacé clic derecho en el lugar y tocá los números que aparecen arriba de todo.
            </p>

            <div class="grid gap-x-4 sm:grid-cols-2">
                <x-campo nombre="latitud" etiqueta="Latitud" :valor="$punto->latitud" required inputmode="decimal" placeholder="-26.1857" />
                <x-campo nombre="longitud" etiqueta="Longitud" :valor="$punto->longitud" required inputmode="decimal" placeholder="-58.1756" />
            </div>

            <div class="flex gap-3">
                <x-boton>Guardar</x-boton>
                <x-boton variante="secundario" :href="route('admin.puntos-retiro.index')">Cancelar</x-boton>
            </div>
        </form>
    </x-tarjeta>
</x-layouts.admin>
