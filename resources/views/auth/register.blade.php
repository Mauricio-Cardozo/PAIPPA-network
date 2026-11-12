<x-guest-layout>
    <h1 class="mb-1 text-lg font-bold text-gray-800">Creá tu cuenta de revendedor</h1>
    <p class="mb-5 text-sm text-gray-600">Con tu cuenta podés contactar a los feriantes por WhatsApp.</p>

    <form method="POST" action="{{ route('register') }}" novalidate>
        @csrf

        <x-campo nombre="name" etiqueta="Nombre y apellido" required autofocus autocomplete="name" />

        <x-campo nombre="email" etiqueta="Correo electrónico" tipo="email" required autocomplete="username" />

        <x-campo nombre="telefono" etiqueta="Teléfono (opcional)" tipo="tel" autocomplete="tel"
                 ayuda="Con código de área, por ejemplo 370 4123456." />

        <x-campo nombre="password" etiqueta="Contraseña" tipo="password" required autocomplete="new-password"
                 ayuda="Al menos 8 caracteres." />

        <x-campo nombre="password_confirmation" etiqueta="Repetí la contraseña" tipo="password" required autocomplete="new-password" />

        <p class="mb-4 text-xs text-gray-500">
            Usamos tus datos solo para que puedas contactar a los feriantes (Ley 25.326).
        </p>

        <div class="flex flex-col-reverse items-center gap-3 sm:flex-row sm:justify-between">
            <a class="text-sm text-gray-600 underline hover:text-gray-900" href="{{ route('login') }}">{{ __('Already registered?') }}</a>
            <x-boton class="w-full sm:w-auto">Registrarme</x-boton>
        </div>
    </form>
</x-guest-layout>
