{{-- Un solo formulario para crear y para editar: si ya existe, es edición. --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-paippa-green">
            {{ $punto->exists ? 'Editar punto de retiro' : 'Nuevo punto de retiro' }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-xl px-4 sm:px-6 lg:px-8">
            <form method="POST" class="space-y-4 rounded-md bg-white p-6 shadow-sm"
                  action="{{ $punto->exists ? route('admin.puntos-retiro.update', $punto) : route('admin.puntos-retiro.store') }}">
                @csrf
                @if ($punto->exists)
                    @method('PUT')
                @endif

                <div>
                    <x-input-label for="feria_id" value="Feria" />
                    <select id="feria_id" name="feria_id" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="">Elegí una feria</option>
                        @foreach ($ferias as $feria)
                            <option value="{{ $feria->id }}" @selected(old('feria_id', $punto->feria_id) == $feria->id)>
                                {{ $feria->nombre }}
                            </option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('feria_id')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="nombre" value="Nombre" />
                    <x-text-input id="nombre" name="nombre" class="mt-1 block w-full"
                                  :value="old('nombre', $punto->nombre)" required maxlength="100" />
                    <x-input-error :messages="$errors->get('nombre')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="direccion" value="Dirección" />
                    <x-text-input id="direccion" name="direccion" class="mt-1 block w-full"
                                  :value="old('direccion', $punto->direccion)" required maxlength="200" />
                    <x-input-error :messages="$errors->get('direccion')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="horario" value="Horario de retiro" />
                    <x-text-input id="horario" name="horario" class="mt-1 block w-full"
                                  :value="old('horario', $punto->horario)" required maxlength="150" />
                    <x-input-error :messages="$errors->get('horario')" class="mt-2" />
                </div>

                <p class="rounded-md bg-green-50 p-3 text-sm text-green-800">
                    Copiá las coordenadas desde Google Maps: hacé clic derecho en el lugar y tocá los números que aparecen arriba de todo.
                </p>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <x-input-label for="latitud" value="Latitud" />
                        <x-text-input id="latitud" name="latitud" class="mt-1 block w-full"
                                      :value="old('latitud', $punto->latitud)" required inputmode="decimal" placeholder="-26.1857" />
                        <x-input-error :messages="$errors->get('latitud')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="longitud" value="Longitud" />
                        <x-text-input id="longitud" name="longitud" class="mt-1 block w-full"
                                      :value="old('longitud', $punto->longitud)" required inputmode="decimal" placeholder="-58.1756" />
                        <x-input-error :messages="$errors->get('longitud')" class="mt-2" />
                    </div>
                </div>

                <div class="flex gap-3">
                    <x-primary-button>Guardar</x-primary-button>
                    <a href="{{ route('admin.puntos-retiro.index') }}" class="px-4 py-2 text-sm text-gray-600 hover:underline">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
