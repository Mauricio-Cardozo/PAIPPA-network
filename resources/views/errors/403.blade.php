{{-- Versión provisoria con el layout de Breeze: la reemplaza PAI-46 con el layout público. --}}
<x-guest-layout>
    <div class="py-6 text-center">
        <p class="text-5xl font-bold text-gray-800">403</p>
        <h1 class="mt-4 text-xl font-semibold text-gray-800">No tenés permiso para entrar acá</h1>
        <p class="mt-2 text-gray-600">Esta sección es solo para otro tipo de usuario.</p>
        <a href="{{ url('/') }}" class="mt-6 inline-block text-sm text-gray-600 underline hover:text-gray-900">Volver al inicio</a>
    </div>
</x-guest-layout>
