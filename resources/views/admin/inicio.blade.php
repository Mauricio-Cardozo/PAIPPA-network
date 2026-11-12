<x-layouts.admin titulo="Inicio">
    <x-slot name="header">
        <h1 class="text-xl font-bold text-gray-800">Panel del administrador</h1>
    </x-slot>

    <x-tarjeta>
        <p class="text-gray-700">Hola, {{ auth()->user()->name }}. Desde el menú podés cargar y validar la información de las ferias.</p>
    </x-tarjeta>
</x-layouts.admin>
