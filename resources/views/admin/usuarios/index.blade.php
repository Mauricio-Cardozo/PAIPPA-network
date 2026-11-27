{{-- HU13: gestión y bloqueo de usuarios. Vista simple; el diseño final es la pantalla P21 de Figma. --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-paippa-green">Usuarios</h2>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">

            @if (session('exito'))
                <div class="mb-4 rounded-md bg-green-50 p-3 text-sm text-green-800">{{ session('exito') }}</div>
            @endif
            @if (session('error'))
                <div class="mb-4 rounded-md bg-red-50 p-3 text-sm text-red-800">{{ session('error') }}</div>
            @endif

            {{-- Buscador por nombre o correo y filtro por rol --}}
            <form method="GET" class="mb-4 flex flex-col gap-2 sm:flex-row">
                <x-text-input name="buscar" :value="request('buscar')" placeholder="Buscar por nombre o correo" class="w-full" />
                <select name="rol" aria-label="Rol" class="rounded-md border-gray-300 shadow-sm">
                    <option value="">Todos los roles</option>
                    @foreach ($roles as $rol)
                        <option value="{{ $rol->value }}" @selected(request('rol') === $rol->value)>{{ $rol->etiqueta() }}</option>
                    @endforeach
                </select>
                <x-secondary-button type="submit">Buscar</x-secondary-button>
            </form>

            @if ($usuarios->isEmpty())
                <p class="rounded-md bg-white p-6 text-center text-gray-500 shadow-sm">No hay usuarios para mostrar.</p>
            @else
                <div class="overflow-x-auto rounded-md bg-white shadow-sm">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 text-left text-gray-600">
                            <tr>
                                <th class="px-4 py-3">Nombre</th>
                                <th class="px-4 py-3">Correo</th>
                                <th class="px-4 py-3">Rol</th>
                                <th class="px-4 py-3">Estado</th>
                                <th class="px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @foreach ($usuarios as $usuario)
                                <tr>
                                    <td class="px-4 py-3 font-medium">{{ $usuario->name }}</td>
                                    <td class="px-4 py-3">{{ $usuario->email }}</td>
                                    <td class="px-4 py-3">{{ $usuario->rol?->etiqueta() }}</td>
                                    <td class="px-4 py-3">
                                        @if ($usuario->estaBloqueado())
                                            <span class="rounded-full bg-red-100 px-2 py-0.5 text-xs font-semibold text-red-800">Bloqueado</span>
                                            {{-- PAI-84: quién lo bloqueó y cuándo --}}
                                            <span class="mt-1 block text-xs text-gray-500">
                                                El {{ $usuario->bloqueado_at->format('d/m/Y H:i') }}
                                                @if ($usuario->bloqueadoPor)
                                                    por {{ $usuario->bloqueadoPor->name }}
                                                @endif
                                            </span>
                                        @else
                                            <span class="rounded-full bg-green-100 px-2 py-0.5 text-xs font-semibold text-green-800">Activo</span>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-right">
                                        {{-- No se ofrece bloquear a uno mismo ni a otro admin --}}
                                        @if ($usuario->estaBloqueado())
                                            <form method="POST" action="{{ route('admin.usuarios.desbloquear', $usuario) }}" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <button class="font-medium text-paippa-green hover:underline">Desbloquear</button>
                                            </form>
                                        @elseif (! $usuario->esAdmin())
                                            <form method="POST" action="{{ route('admin.usuarios.bloquear', $usuario) }}" class="inline"
                                                  onsubmit="return confirm(@js('¿Bloquear a '.$usuario->name.'?'))">
                                                @csrf
                                                @method('PATCH')
                                                <button class="font-medium text-red-700 hover:underline">Bloquear</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">{{ $usuarios->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
