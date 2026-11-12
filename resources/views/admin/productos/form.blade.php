<x-layouts.admin :titulo="$producto->exists ? 'Editar producto' : 'Nuevo producto'">
    <x-slot name="header">
        <h1 class="text-xl font-bold text-gray-800">{{ $producto->exists ? 'Editar producto' : 'Nuevo producto' }}</h1>
    </x-slot>

    <x-tarjeta class="max-w-xl">
        <form method="POST" action="{{ $producto->exists ? route('admin.productos.update', $producto) : route('admin.productos.store') }}">
            @csrf
            @if ($producto->exists)
                @method('PUT')
            @endif

            <x-campo nombre="categoria_id" etiqueta="Categoría" tipo="select" required>
                <option value="">Elegí una categoría</option>
                @foreach ($categorias as $categoria)
                    <option value="{{ $categoria->id }}" @selected(old('categoria_id', $producto->categoria_id) == $categoria->id)>{{ $categoria->nombre }}</option>
                @endforeach
            </x-campo>

            <x-campo nombre="nombre" etiqueta="Nombre" :valor="$producto->nombre" required maxlength="100" />

            <x-campo nombre="unidad" etiqueta="Unidad de venta" :valor="$producto->unidad" required maxlength="30"
                     ayuda="Por ejemplo: caja, bolsa, kilo, docena, atado, maple." />

            <x-campo nombre="descripcion" etiqueta="Descripción (opcional)" tipo="textarea" :valor="$producto->descripcion" />

            <div class="flex gap-3">
                <x-boton>Guardar</x-boton>
                <x-boton variante="secundario" :href="route('admin.productos.index')">Cancelar</x-boton>
            </div>
        </form>
    </x-tarjeta>
</x-layouts.admin>
