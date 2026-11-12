<x-layouts.admin :titulo="$feria->exists ? 'Editar feria' : 'Nueva feria'">
    <x-slot name="header">
        <h1 class="text-xl font-bold text-gray-800">{{ $feria->exists ? 'Editar feria' : 'Nueva feria' }}</h1>
    </x-slot>

    <x-tarjeta class="max-w-xl">
        <form method="POST" action="{{ $feria->exists ? route('admin.ferias.update', $feria) : route('admin.ferias.store') }}">
            @csrf
            @if ($feria->exists)
                @method('PUT')
            @endif

            <x-campo nombre="nombre" etiqueta="Nombre" :valor="$feria->nombre" required maxlength="100" />
            <x-campo nombre="dias_horario" etiqueta="Días y horario" :valor="$feria->dias_horario" required maxlength="150"
                     ayuda="Por ejemplo: miércoles y sábados de 6 a 12." />
            <x-campo nombre="descripcion" etiqueta="Descripción (opcional)" tipo="textarea" :valor="$feria->descripcion" />

            <div class="flex gap-3">
                <x-boton>Guardar</x-boton>
                <x-boton variante="secundario" :href="route('admin.ferias.index')">Cancelar</x-boton>
            </div>
        </form>
    </x-tarjeta>
</x-layouts.admin>
