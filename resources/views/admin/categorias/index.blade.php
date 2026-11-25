<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-paippa-green">Categorías</h2>
            <a href="{{ route('admin.categorias.create') }}"
               class="rounded-md bg-paippa-green px-4 py-2 text-sm font-semibold text-white hover:opacity-90">
                + Nueva categoría
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">

            {{-- Mensajes que manda el controlador con ->with('exito') o ->with('error') --}}
            @if (session('exito'))
                <div class="mb-4 rounded-md bg-green-50 p-3 text-sm text-green-800">{{ session('exito') }}</div>
            @endif
            @if (session('error'))
                <div class="mb-4 rounded-md bg-red-50 p-3 text-sm text-red-800">{{ session('error') }}</div>
            @endif

            {{-- Buscador: manda ?buscar=... y el controlador filtra con when() --}}
            <form method="GET" class="mb-4 flex gap-2">
                <x-text-input name="buscar" :value="request('buscar')" placeholder="Buscar por nombre" class="w-full" />
                <x-secondary-button type="submit">Buscar</x-secondary-button>
            </form>

            @if ($categorias->isEmpty())
                <p class="rounded-md bg-white p-6 text-center text-gray-500 shadow-sm">Todavía no hay categorías.</p>
            @else
                <div class="overflow-x-auto rounded-md bg-white shadow-sm">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 text-left text-gray-600">
                            <tr>
                                <th class="px-4 py-3">Nombre</th>
                                <th class="px-4 py-3">Productos</th>
                                <th class="px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @foreach ($categorias as $categoria)
                                <tr>
                                    <td class="px-4 py-3 font-medium">{{ $categoria->nombre }}</td>
                                    <td class="px-4 py-3">{{ $categoria->productos_count }}</td>
                                    <td class="space-x-3 px-4 py-3 text-right">
                                        <a href="{{ route('admin.categorias.edit', $categoria) }}" class="font-medium text-paippa-green hover:underline">Editar</a>
                                        <form method="POST" action="{{ route('admin.categorias.destroy', $categoria) }}" class="inline"
                                              onsubmit="return confirm('¿Dar de baja {{ $categoria->nombre }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="font-medium text-red-700 hover:underline">Dar de baja</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">{{ $categorias->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
