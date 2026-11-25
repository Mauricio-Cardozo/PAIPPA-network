{{-- Un solo formulario para crear y para editar: si ya existe, es edición. --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-paippa-green">
            {{ $feria->exists ? 'Editar feria' : 'Nueva feria' }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-xl px-4 sm:px-6 lg:px-8">
            <form method="POST" class="space-y-4 rounded-md bg-white p-6 shadow-sm"
                  action="{{ $feria->exists ? route('admin.ferias.update', $feria) : route('admin.ferias.store') }}">
                @csrf
                @if ($feria->exists)
                    @method('PUT')
                @endif

                <div>
                    <x-input-label for="nombre" value="Nombre" />
                    <x-text-input id="nombre" name="nombre" class="mt-1 block w-full"
                                  :value="old('nombre', $feria->nombre)" required maxlength="100" />
                    <x-input-error :messages="$errors->get('nombre')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="dias_horario" value="Días y horario" />
                    <x-text-input id="dias_horario" name="dias_horario" class="mt-1 block w-full"
                                  :value="old('dias_horario', $feria->dias_horario)" required maxlength="150" />
                    <p class="mt-1 text-xs text-gray-500">Por ejemplo: miércoles y sábados de 6 a 12.</p>
                    <x-input-error :messages="$errors->get('dias_horario')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="descripcion" value="Descripción (opcional)" />
                    <textarea id="descripcion" name="descripcion" rows="3"
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">{{ old('descripcion', $feria->descripcion) }}</textarea>
                    <x-input-error :messages="$errors->get('descripcion')" class="mt-2" />
                </div>

                <div class="flex gap-3">
                    <x-primary-button>Guardar</x-primary-button>
                    <a href="{{ route('admin.ferias.index') }}" class="px-4 py-2 text-sm text-gray-600 hover:underline">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
