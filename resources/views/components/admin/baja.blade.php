{{--
    Botón «Dar de baja» con confirmación en un modal (no usa confirm()).
    Uso: <x-admin.baja :accion="route('admin.categorias.destroy', $categoria)" :nombre="$categoria->nombre" />
--}}
@props(['accion', 'nombre'])

@php $modal = 'baja-'.md5($accion); @endphp

<button type="button" class="text-sm font-medium text-red-600 hover:text-red-800"
        x-data @click.prevent="$dispatch('open-modal', '{{ $modal }}')">
    Dar de baja
</button>

<x-modal :name="$modal" maxWidth="md" focusable>
    <form method="POST" action="{{ $accion }}" class="p-6 text-left">
        @csrf
        @method('DELETE')

        <h2 class="text-lg font-semibold text-gray-900">¿Dar de baja «{{ $nombre }}»?</h2>
        <p class="mt-2 text-sm text-gray-600">Deja de mostrarse, pero queda guardado en el historial.</p>

        <div class="mt-6 flex justify-end gap-3">
            <x-boton variante="secundario" type="button" x-on:click="$dispatch('close')">Cancelar</x-boton>
            <x-boton variante="peligro">Dar de baja</x-boton>
        </div>
    </form>
</x-modal>
