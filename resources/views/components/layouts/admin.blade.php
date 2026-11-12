{{-- Layout del panel del administrador. --}}
@props(['titulo' => null])

@php
    $secciones = [
        ['admin.inicio', 'Panel'],
        ['admin.ofertas.index', 'Ofertas'],
        ['admin.feriantes.index', 'Feriantes'],
        ['admin.ferias.index', 'Ferias'],
        ['admin.puntos-retiro.index', 'Puntos de retiro'],
        ['admin.productos.index', 'Productos'],
        ['admin.categorias.index', 'Categorías'],
        ['admin.usuarios.index', 'Usuarios'],
        ['admin.estadisticas', 'Estadísticas'],
    ];
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $titulo ? $titulo.' · ' : '' }}Panel · {{ config('app.name', 'PAIPPA network') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased text-gray-900">
        <div class="min-h-screen bg-gray-50" x-data="{ menu: false }">
            <nav class="bg-verde-800 text-white">
                <div class="mx-auto flex h-14 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                    <a href="{{ route('admin.inicio') }}" class="flex items-center gap-2 font-semibold">
                        <x-application-logo class="h-7 w-7 fill-current text-verde-200" />
                        <span>Panel PAIPPA</span>
                    </a>

                    <div class="flex items-center gap-3 text-sm">
                        <a href="{{ route('catalogo') }}" class="hidden hover:underline sm:inline">Ver catálogo</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="rounded bg-verde-700 px-3 py-1 hover:bg-verde-600">Cerrar sesión</button>
                        </form>
                        <button type="button" class="rounded p-1 hover:bg-verde-700 lg:hidden" @click="menu = ! menu" aria-label="Abrir el menú">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                        </button>
                    </div>
                </div>
            </nav>

            <div class="mx-auto flex max-w-7xl gap-6 px-4 py-6 sm:px-6 lg:px-8">
                {{-- Menú lateral: en el celular se despliega con el botón. --}}
                <aside class="w-full shrink-0 lg:block lg:w-52" :class="menu ? 'block' : 'hidden'">
                    <ul class="space-y-1 rounded-lg bg-white p-2 shadow-sm ring-1 ring-gray-200">
                        @foreach ($secciones as [$ruta, $nombre])
                            @if (Route::has($ruta))
                                <li>
                                    <a href="{{ route($ruta) }}"
                                       @class([
                                           'block rounded-md px-3 py-2 text-sm',
                                           'bg-verde-100 font-semibold text-verde-800' => request()->routeIs(str_replace('.index', '.*', $ruta)),
                                           'text-gray-700 hover:bg-gray-100' => ! request()->routeIs(str_replace('.index', '.*', $ruta)),
                                       ])>{{ $nombre }}</a>
                                </li>
                            @endif
                        @endforeach
                    </ul>
                </aside>

                <main class="min-w-0 flex-1" :class="menu ? 'hidden lg:block' : ''">
                    @isset($header)
                        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                            {{ $header }}
                        </div>
                    @endisset

                    <x-alerta />

                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
