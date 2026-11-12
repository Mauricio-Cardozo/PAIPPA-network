<x-layouts.admin :titulo="$feriante->exists ? 'Editar feriante' : 'Nuevo feriante'">
    <x-slot name="header">
        <h1 class="text-xl font-bold text-gray-800">{{ $feriante->exists ? 'Editar feriante' : 'Nuevo feriante' }}</h1>
    </x-slot>

    <x-tarjeta class="max-w-xl">
        <form method="POST" action="{{ $feriante->exists ? route('admin.feriantes.update', $feriante) : route('admin.feriantes.store') }}">
            @csrf
            @if ($feriante->exists)
                @method('PUT')
            @endif

            <x-campo nombre="feria_id" etiqueta="Feria" tipo="select" required>
                <option value="">Elegí una feria</option>
                @foreach ($ferias as $feria)
                    <option value="{{ $feria->id }}" @selected(old('feria_id', $feriante->feria_id) == $feria->id)>{{ $feria->nombre }}</option>
                @endforeach
            </x-campo>

            <x-campo nombre="nombre" etiqueta="Nombre y apellido" :valor="$feriante->nombre" required maxlength="120" />
            <x-campo nombre="telefono_whatsapp" etiqueta="Teléfono de WhatsApp" tipo="tel" :valor="$feriante->telefono_whatsapp" required
                     ayuda="549 + código de área sin 0 + número sin 15. Ejemplo: +54 9 370 412-3456." />
            <x-campo nombre="descripcion" etiqueta="Descripción (opcional)" tipo="textarea" :valor="$feriante->descripcion" />

            @unless ($feriante->exists)
                <p class="mb-4 text-sm text-gray-600">El feriante queda <strong>pendiente de validar</strong>. Sus ofertas se publican cuando lo valides.</p>
            @endunless

            <div class="flex gap-3">
                <x-boton>Guardar</x-boton>
                <x-boton variante="secundario" :href="route('admin.feriantes.index')">Cancelar</x-boton>
            </div>
        </form>
    </x-tarjeta>
</x-layouts.admin>
