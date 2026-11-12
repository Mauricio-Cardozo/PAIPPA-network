{{-- Buscador por nombre de los listados del panel (formulario GET). --}}
@props(['placeholder' => 'Buscar por nombre'])

<form method="GET" class="mb-4 flex flex-wrap gap-2" role="search">
    <input type="search" name="buscar" value="{{ request('buscar') }}" placeholder="{{ $placeholder }}"
           class="w-full rounded-md border-gray-300 shadow-sm focus:border-verde-500 focus:ring-verde-500 sm:w-auto sm:min-w-[16rem]"
           aria-label="{{ $placeholder }}">
    {{ $slot }}
    <x-boton variante="secundario">Buscar</x-boton>
</form>
