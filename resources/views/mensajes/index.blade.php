<x-layouts.publico titulo="Mensajes">
    <div class="mx-auto max-w-3xl px-4 py-6 sm:px-6">
        <h1 class="mb-4 text-2xl font-bold text-verde-800">Mensajes</h1>

        @if ($conversaciones->isEmpty())
            <div class="rounded-lg border-2 border-dashed border-gray-300 bg-white p-8 text-center text-gray-600">
                Todavía no tenés conversaciones.
                @if (auth()->user()->esRevendedor())
                    Podés escribirle a un feriante desde su perfil.
                @endif
            </div>
        @else
            <ul class="divide-y divide-gray-200 overflow-hidden rounded-lg bg-white shadow-sm ring-1 ring-gray-200">
                @foreach ($conversaciones as $conversacion)
                    @php
                        // Con quién habla: el revendedor ve al feriante y el feriante al revendedor.
                        $otro = $conversacion->revendedor_id === auth()->id() ? $conversacion->feriante?->nombre : $conversacion->revendedor?->name;
                    @endphp
                    <li>
                        <a href="{{ route('mensajes.show', $conversacion) }}" class="flex items-center justify-between gap-3 px-4 py-3 hover:bg-gray-50">
                            <div class="min-w-0">
                                <p class="font-medium text-gray-900">{{ $otro }}</p>
                                <p class="truncate text-sm text-gray-600">{{ $conversacion->ultimoMensaje?->cuerpo }}</p>
                            </div>
                            <div class="shrink-0 text-right">
                                <p class="text-xs text-gray-500">{{ $conversacion->updated_at->diffForHumans() }}</p>
                                @if ($conversacion->no_leidos > 0)
                                    <span class="mt-1 inline-block rounded-full bg-zapallo-500 px-2 text-xs font-bold text-white">{{ $conversacion->no_leidos }} sin leer</span>
                                @endif
                            </div>
                        </a>
                    </li>
                @endforeach
            </ul>
            <div class="mt-4">{{ $conversaciones->links() }}</div>
        @endif
    </div>
</x-layouts.publico>
