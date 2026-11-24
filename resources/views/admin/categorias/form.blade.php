{{-- Un solo formulario para crear y para editar: si la categoría ya existe, es edición. --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-paippa-green">
            {{ $categoria->exists ? 'Editar categoría' : 'Nueva categoría' }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-xl px-4 sm:px-6 lg:px-8">
            <form method="POST" class="space-y-4 rounded-md bg-white p-6 shadow-sm"
                  action="{{ $categoria->exists ? route('admin.categorias.update', $categoria) : route('admin.categorias.store') }}">
                @csrf
                @if ($categoria->exists)
                    @method('PUT')
                @endif

                <div>
                    <x-input-label for="nombre" value="Nombre" />
                    <x-text-input id="nombre" name="nombre" class="mt-1 block w-full"
                                  :value="old('nombre', $categoria->nombre)" required maxlength="100" autofocus />
                    <x-input-error :messages="$errors->get('nombre')" class="mt-2" />
                </div>

                <div class="flex gap-3">
                    <x-primary-button>Guardar</x-primary-button>
                    <a href="{{ route('admin.categorias.index') }}" class="px-4 py-2 text-sm text-gray-600 hover:underline">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
