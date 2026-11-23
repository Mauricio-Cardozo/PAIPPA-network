<nav x-data="{ open: false }"
    class="bg-white border-b border-gray-200 shadow-sm">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="flex justify-between items-center min-h-16">

            <!-- Logo y navegación -->
            <div class="flex items-center gap-8">

                <a href="{{ route('catalogo') }}"
                    class="flex items-center gap-2">

                    <img
                        src="{{ asset('images/paippa-v1-logo.svg') }}"
                        alt="PAIPPA Network"
                        class="h-10 w-auto">
                </a>

                <!-- Menú de escritorio -->
                <div class="hidden sm:flex items-center gap-6">

                    <a href="{{ route('catalogo') }}"
                        class="text-sm font-medium
                       {{ request()->routeIs('catalogo')
                            ? 'text-paippa-green'
                            : 'text-gray-600 hover:text-paippa-green' }}">
                        Catálogo
                    </a>

                    @if(auth()->user()->esAdmin())
                    <a href="{{ route('admin.inicio') }}"
                        class="text-sm font-medium
                           {{ request()->routeIs('admin.*')
                                ? 'text-paippa-green'
                                : 'text-gray-600 hover:text-paippa-green' }}">
                        Administración
                    </a>
                    @endif

                </div>
            </div>

            <!-- Usuario y cierre de sesión -->
            <div class="hidden sm:flex items-center gap-4">

                <span class="text-sm text-gray-600">
                    {{ auth()->user()->name }}
                </span>

                <a href="{{ route('profile.edit') }}"
                    class="text-sm text-paippa-green hover:underline">
                    Mi perfil
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <button type="submit"
                        class="bg-paippa-green text-white
                                   px-4 py-2 rounded-md text-sm
                                   hover:opacity-90 transition">
                        Salir
                    </button>
                </form>

            </div>

            <!-- Botón para móviles -->
            <div class="sm:hidden">
                <button @click="open = !open"
                    type="button"
                    class="text-paippa-green p-2"
                    aria-label="Abrir menú"
                    :aria-expanded="open.toString()">

                    <svg class="w-6 h-6"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24">

                        <path stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>

        </div>
    </div>

    <!-- Menú móvil -->
    <div x-show="open"
        x-cloak
        class="sm:hidden bg-white border-t border-gray-200">

        <div class="px-4 py-3 space-y-3">

            <a href="{{ route('catalogo') }}"
                class="block text-sm text-paippa-green">
                Catálogo
            </a>

            @if(auth()->user()->esAdmin())
            <a href="{{ route('admin.inicio') }}"
                class="block text-sm text-paippa-green">
                Administración
            </a>
            @endif

            <a href="{{ route('profile.edit') }}"
                class="block text-sm text-paippa-green">
                Mi perfil
            </a>

            <div class="text-sm text-gray-600">
                {{ auth()->user()->name }}
            </div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf

                <button type="submit"
                    class="text-sm text-red-600">
                    Cerrar sesión
                </button>
            </form>

        </div>
    </div>

</nav>