<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'PAIPPA network') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="flex min-h-screen flex-col items-center bg-verde-50 px-4 pt-8 sm:justify-center sm:pt-0">
            <a href="{{ route('catalogo') }}" class="flex items-center gap-2">
                <x-application-logo class="h-12 w-12 fill-current text-verde-600" />
                <span class="text-xl font-bold text-verde-800">PAIPPA <span class="font-normal text-gray-600">network</span></span>
            </a>

            <div class="mt-6 w-full overflow-hidden bg-white px-6 py-5 shadow-md sm:max-w-md sm:rounded-lg">
                {{ $slot }}
            </div>

            <a href="{{ route('catalogo') }}" class="mt-4 text-sm text-verde-700 hover:underline">← Volver al catálogo</a>
        </div>
    </body>
</html>
