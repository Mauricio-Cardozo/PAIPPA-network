<x-guest-layout>

    <!-- Título del formulario -->
    <div class="mb-6 text-center">
        <h1 class="text-2xl font-bold text-paippa-green">
            PAIPPA Network
        </h1>
    </div>

    <!-- Mensajes de sesión -->
    <x-auth-session-status
        class="mb-4"
        :status="session('status')" />

    <!-- Formulario de inicio de sesión -->
    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Correo electrónico -->
        <div>
            <x-input-label
                for="email"
                value="Correo electrónico" />

            <x-text-input
                id="email"
                class="block mt-1 w-full focus:border-paippa-green focus:ring-paippa-green"
                type="email"
                name="email"
                :value="old('email')"
                required
                autofocus
                autocomplete="username" />

            <x-input-error
                :messages="$errors->get('email')"
                class="mt-2" />
        </div>

        <!-- Contraseña -->
        <div class="mt-4">
            <x-input-label
                for="password"
                value="Contraseña" />

            <x-text-input
                id="password"
                class="block mt-1 w-full focus:border-paippa-green focus:ring-paippa-green"
                type="password"
                name="password"
                required
                autocomplete="current-password" />

            <x-input-error
                :messages="$errors->get('password')"
                class="mt-2" />
        </div>

        <!-- Recordar sesión -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input
                    id="remember_me"
                    type="checkbox"
                    name="remember"
                    class="rounded border-gray-300 text-paippa-green focus:ring-paippa-green">

                <span class="ms-2 text-sm text-gray-600">
                    Recordarme
                </span>
            </label>
        </div>

        <!-- Acciones -->
        <div class="flex items-center justify-between mt-6">

            @if (Route::has('password.request'))
            <a
                href="{{ route('password.request') }}"
                class="text-sm text-paippa-green hover:underline">
                ¿Olvidaste tu contraseña?
            </a>
            @endif

            <button
                type="submit"
                class="inline-flex items-center px-5 py-2.5 bg-paippa-green border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-paippa-green focus:ring-offset-2 transition">
                Ingresar
            </button>

        </div>
    </form>

</x-guest-layout>