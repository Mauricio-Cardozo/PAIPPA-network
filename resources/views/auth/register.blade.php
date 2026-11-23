<x-guest-layout>

    <!-- Encabezado -->
    <div class="mb-6 text-center">
        <h1 class="text-2xl font-bold text-[#2F7D32]">
            Crear cuenta
        </h1>
    </div>

    <!-- Formulario -->
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Nombre -->
        <div>
            <x-input-label for="name" value="Nombre completo" />

            <x-text-input
                id="name"
                class="block mt-1 w-full focus:border-[#2F7D32] focus:ring-[#2F7D32]"
                type="text"
                name="name"
                :value="old('name')"
                required
                autofocus
                autocomplete="name" />

            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Correo electrónico -->
        <div class="mt-4">
            <x-input-label for="email" value="Correo electrónico" />

            <x-text-input
                id="email"
                class="block mt-1 w-full focus:border-[#2F7D32] focus:ring-[#2F7D32]"
                type="email"
                name="email"
                :value="old('email')"
                required
                autocomplete="username" />

            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Contraseña -->
        <div class="mt-4">
            <x-input-label for="password" value="Contraseña" />

            <x-text-input
                id="password"
                class="block mt-1 w-full focus:border-[#2F7D32] focus:ring-[#2F7D32]"
                type="password"
                name="password"
                required
                autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirmar contraseña -->
        <div class="mt-4">
            <x-input-label
                for="password_confirmation"
                value="Confirmar contraseña" />

            <x-text-input
                id="password_confirmation"
                class="block mt-1 w-full focus:border-[#2F7D32] focus:ring-[#2F7D32]"
                type="password"
                name="password_confirmation"
                required
                autocomplete="new-password" />

            <x-input-error
                :messages="$errors->get('password_confirmation')"
                class="mt-2" />
        </div>

        <!-- Acciones -->
        <div class="flex items-center justify-between gap-3 mt-6">

            <a
                href="{{ route('login') }}"
                class="text-sm text-[#2F7D32] hover:underline">
                ¿Ya tenés cuenta?
            </a>

            <button
                type="submit"
                class="inline-flex items-center px-5 py-2.5 bg-[#2F7D32] text-white text-xs font-semibold uppercase tracking-wider rounded-md hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-[#2F7D32] focus:ring-offset-2 transition">
                Registrarme
            </button>

        </div>
    </form>

</x-guest-layout>