{{-- Layout público: catálogo, perfil, mapa y la cuenta del usuario. --}}
@props(['titulo' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $titulo ? $titulo.' · ' : '' }}{{ config('app.name', 'PAIPPA network') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        {{ $head ?? '' }}
    </head>
    <body class="font-sans antialiased text-gray-900">
        <div class="flex min-h-screen flex-col bg-gray-50">
            @include('layouts.navigation')

            @isset($header)
                <header class="bg-white shadow-sm">
                    <div class="mx-auto max-w-7xl px-4 py-5 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <main class="flex-1">
                <div class="mx-auto max-w-7xl px-4 pt-4 sm:px-6 lg:px-8">
                    <x-alerta />
                </div>
                {{ $slot }}
            </main>

            <footer class="border-t border-gray-200 bg-white">
                <div class="mx-auto max-w-7xl px-4 py-6 text-center text-xs text-gray-500 sm:px-6 lg:px-8">
                    PAIPPA network · Ferias del PAIPPA en Formosa Capital ·
                    Los precios son estimados y los publica cada feriante.
                </div>
            </footer>
        </div>
    </body>
</html>
