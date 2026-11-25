{{-- Un solo formulario para crear y para editar: si ya existe, es edición. --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-paippa-green">
            {{ $feriante->exists ? 'Editar feriante' : 'Nuevo feriante' }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-xl px-4 sm:px-6 lg:px-8">
            <form method="POST" class="space-y-4 rounded-md bg-white p-6 shadow-sm"
                  action="{{ $feriante->exists ? route('admin.feriantes.update', $feriante) : route('admin.feriantes.store') }}">
                @csrf
                @if ($feriante->exists)
                    @method('PUT')
                @endif

                <div>
                    <x-input-label for="feria_id" value="Feria" />
                    <select id="feria_id" name="feria_id" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="">Elegí una feria</option>
                        @foreach ($ferias as $feria)
                            <option value="{{ $feria->id }}" @selected(old('feria_id', $feriante->feria_id) == $feria->id)>
                                {{ $feria->nombre }}
                            </option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('feria_id')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="nombre" value="Nombre y apellido" />
                    <x-text-input id="nombre" name="nombre" class="mt-1 block w-full"
                                  :value="old('nombre', $feriante->nombre)" required maxlength="120" />
                    <x-input-error :messages="$errors->get('nombre')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="procedencia" value="Procedencia" />
                    <x-text-input id="procedencia" name="procedencia" class="mt-1 block w-full"
                                  :value="old('procedencia', $feriante->procedencia)" required maxlength="150"
                                  placeholder="Localidad o colonia de la producción" />
                    <x-input-error :messages="$errors->get('procedencia')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="telefono_whatsapp" value="Teléfono de WhatsApp" />
                    <x-text-input id="telefono_whatsapp" name="telefono_whatsapp" type="tel" class="mt-1 block w-full"
                                  :value="old('telefono_whatsapp', $feriante->telefono_whatsapp)" required />
                    <p class="mt-1 text-xs text-gray-500">549 + código de área sin 0 + número sin 15. Ejemplo: +54 9 370 412-3456.</p>
                    <x-input-error :messages="$errors->get('telefono_whatsapp')" class="mt-2" />
                </div>

                {{-- RN-06: el número se muestra solo si el feriante lo autoriza --}}
                <label for="autoriza_whatsapp" class="flex items-start gap-2 text-sm text-gray-700">
                    <input id="autoriza_whatsapp" name="autoriza_whatsapp" type="checkbox" value="1"
                           class="mt-0.5 rounded border-gray-300 text-paippa-green shadow-sm"
                           @checked(old('autoriza_whatsapp', $feriante->autoriza_whatsapp))>
                    <span>Autoriza mostrar su número de WhatsApp. Si no lo autoriza, los compradores lo contactan a través del área de Comercialización del PAIPPA.</span>
                </label>

                <div>
                    <x-input-label for="descripcion" value="Descripción (opcional)" />
                    <textarea id="descripcion" name="descripcion" rows="3"
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">{{ old('descripcion', $feriante->descripcion) }}</textarea>
                    <x-input-error :messages="$errors->get('descripcion')" class="mt-2" />
                </div>

                @unless ($feriante->exists)
                    <p class="text-sm text-gray-600">El feriante queda <strong>pendiente de validar</strong>. Sus ofertas se publican cuando lo valides.</p>
                @endunless

                <div class="flex gap-3">
                    <x-primary-button>Guardar</x-primary-button>
                    <a href="{{ route('admin.feriantes.index') }}" class="px-4 py-2 text-sm text-gray-600 hover:underline">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
