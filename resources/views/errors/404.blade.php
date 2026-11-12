<x-layouts.publico titulo="No encontrado">
    <div class="mx-auto max-w-xl px-4 py-16 text-center">
        <p class="text-5xl font-bold text-verde-700">404</p>
        <h1 class="mt-4 text-xl font-semibold text-gray-800">No encontramos lo que buscabas</h1>
        <p class="mt-2 text-gray-600">Puede que la oferta haya vencido o que el enlace esté mal.</p>
        <x-boton class="mt-6" :href="route('catalogo')">Volver al catálogo</x-boton>
    </div>
</x-layouts.publico>
