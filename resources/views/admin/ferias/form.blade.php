{{-- Un solo formulario para crear y para editar: si ya existe, es edición. --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-paippa-green">
            {{ $feria->exists ? 'Editar feria' : 'Nueva feria' }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-xl px-4 sm:px-6 lg:px-8">
            {{-- Alpine muestra «Días» para las fijas y «Fecha» para las itinerantes --}}
            <form method="POST" class="space-y-4 rounded-md bg-white p-6 shadow-sm"
                  x-data="{ tipo: '{{ old('tipo', $feria->tipo?->value ?? 'fija') }}' }"
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
                    <x-input-label for="tipo" value="Tipo de feria" />
                    <select id="tipo" name="tipo" x-model="tipo" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        @foreach (\App\Enums\TipoFeria::cases() as $tipo)
                            <option value="{{ $tipo->value }}">{{ $tipo->etiqueta() }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-gray-500">Fija: siempre en el mismo lugar ciertos días. Itinerante: en un barrio, en una fecha puntual.</p>
                    <x-input-error :messages="$errors->get('tipo')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="direccion" value="Dirección" />
                    <x-text-input id="direccion" name="direccion" class="mt-1 block w-full"
                                  :value="old('direccion', $feria->direccion)" required maxlength="150" />
                    <x-input-error :messages="$errors->get('direccion')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="barrio" value="Barrio" />
                    <x-text-input id="barrio" name="barrio" class="mt-1 block w-full"
                                  :value="old('barrio', $feria->barrio)" required maxlength="100" />
                    <x-input-error :messages="$errors->get('barrio')" class="mt-2" />
                </div>

                <div x-show="tipo === 'fija'">
                    <x-input-label for="dias" value="Días" />
                    <x-text-input id="dias" name="dias" class="mt-1 block w-full" maxlength="50"
                                  :value="old('dias', $feria->dias)" placeholder="Miércoles y sábados" />
                    <x-input-error :messages="$errors->get('dias')" class="mt-2" />
                </div>

                <div x-show="tipo === 'itinerante'">
                    <x-input-label for="fecha" value="Fecha" />
                    <x-text-input id="fecha" name="fecha" type="date" class="mt-1 block w-full"
                                  :value="old('fecha', $feria->fecha?->format('Y-m-d'))" />
                    <x-input-error :messages="$errors->get('fecha')" class="mt-2" />
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <x-input-label for="hora_inicio" value="Hora de inicio" />
                        <x-text-input id="hora_inicio" name="hora_inicio" type="time" class="mt-1 block w-full" required
                                      :value="old('hora_inicio', $feria->hora_inicio ? substr($feria->hora_inicio, 0, 5) : null)" />
                        <x-input-error :messages="$errors->get('hora_inicio')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="hora_fin" value="Hora de fin" />
                        <x-text-input id="hora_fin" name="hora_fin" type="time" class="mt-1 block w-full" required
                                      :value="old('hora_fin', $feria->hora_fin ? substr($feria->hora_fin, 0, 5) : null)" />
                        <x-input-error :messages="$errors->get('hora_fin')" class="mt-2" />
                    </div>
                </div>

                <p class="rounded-md bg-green-50 p-3 text-sm text-green-800">
                    Ubicación en el mapa (opcional): copiá las coordenadas desde Google Maps con clic derecho sobre el lugar.
                </p>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <x-input-label for="latitud" value="Latitud" />
                        <x-text-input id="latitud" name="latitud" class="mt-1 block w-full" inputmode="decimal"
                                      :value="old('latitud', $feria->latitud)" placeholder="-26.1857" />
                        <x-input-error :messages="$errors->get('latitud')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="longitud" value="Longitud" />
                        <x-text-input id="longitud" name="longitud" class="mt-1 block w-full" inputmode="decimal"
                                      :value="old('longitud', $feria->longitud)" placeholder="-58.1756" />
                        <x-input-error :messages="$errors->get('longitud')" class="mt-2" />
                    </div>
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
