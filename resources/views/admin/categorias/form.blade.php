{{-- Un solo formulario para crear y para editar. --}}
<x-layouts.admin :titulo="$categoria->exists ? 'Editar categoría' : 'Nueva categoría'">
    <x-slot name="header">
        <h1 class="text-xl font-bold text-gray-800">{{ $categoria->exists ? 'Editar categoría' : 'Nueva categoría' }}</h1>
    </x-slot>

    <x-tarjeta class="max-w-xl">
        <form method="POST" action="{{ $categoria->exists ? route('admin.categorias.update', $categoria) : route('admin.categorias.store') }}">
            @csrf
            @if ($categoria->exists)
                @method('PUT')
            @endif

            <x-campo nombre="nombre" etiqueta="Nombre" :valor="$categoria->nombre" required maxlength="100" autofocus />

            <div class="flex gap-3">
                <x-boton>Guardar</x-boton>
                <x-boton variante="secundario" :href="route('admin.categorias.index')">Cancelar</x-boton>
            </div>
        </form>
    </x-tarjeta>
</x-layouts.admin>
