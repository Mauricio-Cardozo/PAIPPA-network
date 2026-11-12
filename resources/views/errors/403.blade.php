<x-layouts.publico titulo="Sin permiso">
    <div class="mx-auto max-w-xl px-4 py-16 text-center">
        <p class="text-5xl font-bold text-verde-700">403</p>
        <h1 class="mt-4 text-xl font-semibold text-gray-800">No tenés permiso para entrar acá</h1>
        <p class="mt-2 text-gray-600">Esta sección es solo para otro tipo de usuario.</p>
        <x-boton class="mt-6" :href="route('catalogo')">Volver al catálogo</x-boton>
    </div>
</x-layouts.publico>
