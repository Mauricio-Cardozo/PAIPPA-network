{{-- Un solo formulario para crear y para editar: si el producto ya existe, es edición. --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-paippa-green">
            {{ $producto->exists ? 'Editar producto' : 'Nuevo producto' }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-xl px-4 sm:px-6 lg:px-8">
            <form method="POST" class="space-y-4 rounded-md bg-white p-6 shadow-sm"
                  action="{{ $producto->exists ? route('admin.productos.update', $producto) : route('admin.productos.store') }}">
                @csrf
                @if ($producto->exists)
                    @method('PUT')
                @endif

                <div>
                    <x-input-label for="categoria_id" value="Categoría" />
                    <select id="categoria_id" name="categoria_id" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="">Elegí una categoría</option>
                        @foreach ($categorias as $categoria)
                            <option value="{{ $categoria->id }}" @selected(old('categoria_id', $producto->categoria_id) == $categoria->id)>
                                {{ $categoria->nombre }}
                            </option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('categoria_id')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="nombre" value="Nombre" />
                    <x-text-input id="nombre" name="nombre" class="mt-1 block w-full"
                                  :value="old('nombre', $producto->nombre)" required maxlength="100" />
                    <x-input-error :messages="$errors->get('nombre')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="unidad" value="Unidad de venta" />
                    <select id="unidad" name="unidad" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="">Elegí una unidad</option>
                        @foreach (\App\Enums\Unidad::cases() as $unidad)
                            <option value="{{ $unidad->value }}" @selected(old('unidad', $producto->unidad?->value) === $unidad->value)>
                                {{ $unidad->etiqueta() }}
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-gray-500">La cantidad y el precio de cada oferta se expresan en esta unidad.</p>
                    <x-input-error :messages="$errors->get('unidad')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="descripcion" value="Descripción (opcional)" />
                    <textarea id="descripcion" name="descripcion" rows="3"
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">{{ old('descripcion', $producto->descripcion) }}</textarea>
                    <x-input-error :messages="$errors->get('descripcion')" class="mt-2" />
                </div>

                <div class="flex gap-3">
                    <x-primary-button>Guardar</x-primary-button>
                    <a href="{{ route('admin.productos.index') }}" class="px-4 py-2 text-sm text-gray-600 hover:underline">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
