<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-paippa-green">Ferias</h2>
            <a href="{{ route('admin.ferias.create') }}"
               class="rounded-md bg-paippa-green px-4 py-2 text-sm font-semibold text-white hover:opacity-90">
                + Nueva feria
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">

            @if (session('exito'))
                <div class="mb-4 rounded-md bg-green-50 p-3 text-sm text-green-800">{{ session('exito') }}</div>
            @endif
            @if (session('error'))
                <div class="mb-4 rounded-md bg-red-50 p-3 text-sm text-red-800">{{ session('error') }}</div>
            @endif

            <form method="GET" class="mb-4 flex gap-2">
                <x-text-input name="buscar" :value="request('buscar')" placeholder="Buscar por nombre" class="w-full" />
                <x-secondary-button type="submit">Buscar</x-secondary-button>
            </form>

            @if ($ferias->isEmpty())
                <p class="rounded-md bg-white p-6 text-center text-gray-500 shadow-sm">Todavía no hay ferias.</p>
            @else
                <div class="overflow-x-auto rounded-md bg-white shadow-sm">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 text-left text-gray-600">
                            <tr>
                                <th class="px-4 py-3">Nombre</th>
                                <th class="px-4 py-3">Tipo</th>
                                <th class="px-4 py-3">Barrio</th>
                                <th class="px-4 py-3">Cuándo</th>
                                <th class="px-4 py-3">Puntos</th>
                                <th class="px-4 py-3">Feriantes</th>
                                <th class="px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @foreach ($ferias as $feria)
                                <tr>
                                    <td class="px-4 py-3 font-medium">{{ $feria->nombre }}</td>
                                    <td class="px-4 py-3">{{ $feria->tipo?->etiqueta() }}</td>
                                    <td class="px-4 py-3">{{ $feria->barrio }}</td>
                                    <td class="px-4 py-3">{{ $feria->cuando }}</td>
                                    <td class="px-4 py-3">{{ $feria->puntos_retiro_count }}</td>
                                    <td class="px-4 py-3">{{ $feria->feriantes_count }}</td>
                                    <td class="space-x-3 px-4 py-3 text-right">
                                        <a href="{{ route('admin.ferias.edit', $feria) }}" class="font-medium text-paippa-green hover:underline">Editar</a>
                                        <form method="POST" action="{{ route('admin.ferias.destroy', $feria) }}" class="inline"
                                              onsubmit="return confirm('¿Dar de baja {{ $feria->nombre }}?')">
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

                <div class="mt-4">{{ $ferias->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
