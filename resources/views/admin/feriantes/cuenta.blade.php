<x-layouts.admin titulo="Crear cuenta del feriante">
    <x-slot name="header">
        <h1 class="text-xl font-bold text-gray-800">Crear cuenta de {{ $feriante->nombre }}</h1>
    </x-slot>

    <x-tarjeta class="max-w-xl">
        <p class="mb-4 text-sm text-gray-600">
            Con su cuenta, el feriante puede leer y responder los mensajes de los revendedores.
            Le vamos a mandar un correo para que elija su contraseña.
        </p>

        <form method="POST" action="{{ route('admin.feriantes.cuenta.store', $feriante) }}">
            @csrf
            <x-campo nombre="email" etiqueta="Correo electrónico del feriante" tipo="email" required autofocus />

            <div class="flex gap-3">
                <x-boton>Crear cuenta</x-boton>
                <x-boton variante="secundario" :href="route('admin.feriantes.index')">Cancelar</x-boton>
            </div>
        </form>
    </x-tarjeta>
</x-layouts.admin>
