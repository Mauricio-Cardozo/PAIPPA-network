<x-layouts.admin titulo="Usuarios">
    <x-slot name="header">
        <h1 class="text-xl font-bold text-gray-800">Usuarios</h1>
    </x-slot>

    <x-admin.buscador placeholder="Buscar por nombre o correo">
        <select name="rol" class="rounded-md border-gray-300 shadow-sm focus:border-verde-500 focus:ring-verde-500" aria-label="Rol">
            <option value="">Todos los roles</option>
            @foreach ($roles as $rol)
                <option value="{{ $rol->value }}" @selected(request('rol') === $rol->value)>{{ $rol->etiqueta() }}</option>
            @endforeach
        </select>
    </x-admin.buscador>

    @if ($usuarios->isEmpty())
        <x-admin.vacio>No hay usuarios para mostrar.</x-admin.vacio>
    @else
        <x-admin.tabla :columnas="['Nombre', 'Correo', 'Rol', 'Estado']">
            @foreach ($usuarios as $usuario)
                <tr>
                    <td class="px-4 py-3 font-medium" data-etiqueta="Nombre">{{ $usuario->name }}</td>
                    <td class="px-4 py-3" data-etiqueta="Correo">{{ $usuario->email }}</td>
                    <td class="px-4 py-3" data-etiqueta="Rol">{{ $usuario->rol->etiqueta() }}</td>
                    <td class="px-4 py-3" data-etiqueta="Estado">
                        @if ($usuario->estaBloqueado())
                            <div>
                                <span class="rounded-full bg-red-100 px-2 py-0.5 text-xs font-semibold text-red-800">Bloqueado</span>
                                <span class="block text-xs text-gray-500">
                                    el {{ $usuario->bloqueado_at->format('d/m/Y H:i') }}
                                    por {{ $usuario->bloqueadoPor?->name ?? 'un administrador eliminado' }}
                                </span>
                            </div>
                        @else
                            <span class="rounded-full bg-verde-100 px-2 py-0.5 text-xs font-semibold text-verde-800">Activo</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right">
                        @if (! $usuario->esAdmin())
                            <form method="POST" class="inline"
                                  action="{{ $usuario->estaBloqueado() ? route('admin.usuarios.desbloquear', $usuario) : route('admin.usuarios.bloquear', $usuario) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="text-sm font-medium {{ $usuario->estaBloqueado() ? 'text-verde-700 hover:text-verde-900' : 'text-red-600 hover:text-red-800' }}">
                                    {{ $usuario->estaBloqueado() ? 'Desbloquear' : 'Bloquear' }}
                                </button>
                            </form>
                        @endif
                    </td>
                </tr>
            @endforeach
        </x-admin.tabla>

        <div class="mt-4">{{ $usuarios->links() }}</div>
    @endif
</x-layouts.admin>
