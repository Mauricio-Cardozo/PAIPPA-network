{{--
    Menú principal. Muestra solo lo que corresponde a cada rol:
    - visitante: Catálogo, Mapa, Ingresar y Registrarse
    - revendedor y feriante: Catálogo, Mapa y su cuenta
    - admin: además, el enlace al Panel
--}}
@php
    $enlaces = [['catalogo', 'Catálogo']];
    if (Route::has('mapa')) {
        $enlaces[] = ['mapa', 'Mapa'];
    }
    if (Route::has('mensajes.index') && auth()->check() && ! auth()->user()->esAdmin()) {
        $enlaces[] = ['mensajes.index', 'Mensajes'];
    }
    if (auth()->user()?->esAdmin()) {
        $enlaces[] = ['admin.inicio', 'Panel'];
    }
@endphp

<nav x-data="{ open: false }" class="border-b border-gray-200 bg-white">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 justify-between">
            <div class="flex">
                <a href="{{ route('catalogo') }}" class="flex shrink-0 items-center gap-2">
                    <x-application-logo class="h-8 w-8 fill-current text-verde-600" />
                    <span class="font-bold text-verde-800">PAIPPA <span class="font-normal text-gray-600">network</span></span>
                </a>

                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    @foreach ($enlaces as [$ruta, $nombre])
                        <x-nav-link :href="route($ruta)" :active="request()->routeIs($ruta)">{{ $nombre }}</x-nav-link>
                    @endforeach
                </div>
            </div>

            <div class="hidden sm:ms-6 sm:flex sm:items-center sm:gap-3">
                @auth
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center rounded-md border border-transparent bg-white px-3 py-2 text-sm font-medium leading-4 text-gray-600 transition hover:text-gray-800 focus:outline-none">
                                <div>{{ Auth::user()->name }}</div>
                                <div class="ms-1">
                                    <svg class="h-4 w-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                                </div>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <x-dropdown-link :href="route('profile.edit')">{{ __('Profile') }}</x-dropdown-link>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">{{ __('Log Out') }}</x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                @else
                    <a href="{{ route('login') }}" class="text-sm font-medium text-gray-700 hover:text-verde-700">Ingresar</a>
                    <x-boton :href="route('register')">Registrarse</x-boton>
                @endauth
            </div>

            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center rounded-md p-2 text-gray-500 transition hover:bg-gray-100 focus:outline-none" aria-label="Abrir el menú">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Menú del celular --}}
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="space-y-1 pb-3 pt-2">
            @foreach ($enlaces as [$ruta, $nombre])
                <x-responsive-nav-link :href="route($ruta)" :active="request()->routeIs($ruta)">{{ $nombre }}</x-responsive-nav-link>
            @endforeach
        </div>

        <div class="border-t border-gray-200 pb-1 pt-4">
            @auth
                <div class="px-4">
                    <div class="text-base font-medium text-gray-800">{{ Auth::user()->name }}</div>
                    <div class="text-sm font-medium text-gray-500">{{ Auth::user()->email }}</div>
                </div>
                <div class="mt-3 space-y-1">
                    <x-responsive-nav-link :href="route('profile.edit')">{{ __('Profile') }}</x-responsive-nav-link>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <x-responsive-nav-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">{{ __('Log Out') }}</x-responsive-nav-link>
                    </form>
                </div>
            @else
                <div class="space-y-1">
                    <x-responsive-nav-link :href="route('login')">Ingresar</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('register')">Registrarse</x-responsive-nav-link>
                </div>
            @endauth
        </div>
    </div>
</nav>
