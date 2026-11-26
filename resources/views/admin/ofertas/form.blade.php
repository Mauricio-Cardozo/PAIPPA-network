{{-- Un solo formulario para crear y para editar: si ya existe, es edición. --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-paippa-green">
            {{ $oferta->exists ? 'Editar oferta' : 'Nueva oferta' }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-xl px-4 sm:px-6 lg:px-8">
            @if ($feriantes->isEmpty())
                <p class="rounded-md bg-white p-6 text-center text-gray-500 shadow-sm">
                    Para cargar ofertas primero tiene que haber un feriante validado.
                    <a href="{{ route('admin.feriantes.index') }}" class="font-medium text-paippa-green hover:underline">Ir a feriantes</a>
                </p>
            @else
                <form method="POST" class="space-y-4 rounded-md bg-white p-6 shadow-sm"
                      action="{{ $oferta->exists ? route('admin.ofertas.update', $oferta) : route('admin.ofertas.store') }}">
                    @csrf
                    @if ($oferta->exists)
                        @method('PUT')
                    @endif

                    <div>
                        <x-input-label for="feriante_id" value="Feriante (solo validados)" />
                        <select id="feriante_id" name="feriante_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            <option value="">Elegí un feriante</option>
                            @foreach ($feriantes as $feriante)
                                <option value="{{ $feriante->id }}" @selected(old('feriante_id', $oferta->feriante_id) == $feriante->id)>
                                    {{ $feriante->nombre }} · {{ $feriante->feria?->nombre }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('feriante_id')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="producto_id" value="Producto" />
                        <select id="producto_id" name="producto_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            <option value="">Elegí un producto</option>
                            @foreach ($productos as $producto)
                                <option value="{{ $producto->id }}" @selected(old('producto_id', $oferta->producto_id) == $producto->id)>
                                    {{ $producto->nombre }} (por {{ $producto->unidad?->value }})
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-gray-500">El precio y la cantidad van en la unidad del producto.</p>
                        <x-input-error :messages="$errors->get('producto_id')" class="mt-2" />
                    </div>

                    {{-- Agrupados por feria: tiene que ser un punto de la feria del feriante --}}
                    <div>
                        <x-input-label for="punto_retiro_id" value="Punto de retiro" />
                        <select id="punto_retiro_id" name="punto_retiro_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            <option value="">Elegí un punto de retiro</option>
                            @foreach ($ferias as $feria)
                                <optgroup label="{{ $feria->nombre }}">
                                    @foreach ($feria->puntosRetiro as $punto)
                                        <option value="{{ $punto->id }}" @selected(old('punto_retiro_id', $oferta->punto_retiro_id) == $punto->id)>{{ $punto->nombre }}</option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-gray-500">Tiene que ser un punto de la misma feria del feriante.</p>
                        <x-input-error :messages="$errors->get('punto_retiro_id')" class="mt-2" />
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <x-input-label for="precio" value="Precio estimado ($)" />
                            <x-text-input id="precio" name="precio" class="mt-1 block w-full" inputmode="decimal" required
                                          :value="old('precio', $oferta->precio)" placeholder="2500" />
                            <x-input-error :messages="$errors->get('precio')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="cantidad_disponible" value="Cantidad disponible" />
                            <x-text-input id="cantidad_disponible" name="cantidad_disponible" class="mt-1 block w-full" inputmode="decimal" required
                                          :value="old('cantidad_disponible', $oferta->cantidad_disponible ?? 0)" />
                            <x-input-error :messages="$errors->get('cantidad_disponible')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="disponible_desde" value="Disponible desde" />
                            <x-text-input id="disponible_desde" name="disponible_desde" type="date" class="mt-1 block w-full" required
                                          :value="old('disponible_desde', $oferta->disponible_desde?->toDateString())" />
                            <x-input-error :messages="$errors->get('disponible_desde')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="vigente_hasta" value="Vigente hasta" />
                            <x-text-input id="vigente_hasta" name="vigente_hasta" type="date" class="mt-1 block w-full" required
                                          :value="old('vigente_hasta', $oferta->vigente_hasta?->toDateString())" />
                            <x-input-error :messages="$errors->get('vigente_hasta')" class="mt-2" />
                        </div>
                    </div>
                    <p class="text-xs text-gray-500">La oferta vale como máximo 7 días: después hay que actualizarla.</p>

                    <div class="space-y-2">
                        <label class="flex items-center gap-2 text-sm text-gray-700">
                            <input type="checkbox" name="promocion" value="1" class="rounded border-gray-300 text-paippa-green shadow-sm"
                                   @checked(old('promocion', $oferta->promocion))>
                            Está en promoción
                        </label>
                        <label class="flex items-center gap-2 text-sm text-gray-700">
                            <input type="checkbox" name="entrega_domicilio" value="1" class="rounded border-gray-300 text-paippa-green shadow-sm"
                                   @checked(old('entrega_domicilio', $oferta->entrega_domicilio))>
                            Entrega a domicilio
                        </label>
                    </div>

                    <div class="flex gap-3">
                        <x-primary-button>Guardar</x-primary-button>
                        <a href="{{ route('admin.ofertas.index') }}" class="px-4 py-2 text-sm text-gray-600 hover:underline">Cancelar</a>
                    </div>
                </form>
            @endif
        </div>
    </div>
</x-app-layout>
