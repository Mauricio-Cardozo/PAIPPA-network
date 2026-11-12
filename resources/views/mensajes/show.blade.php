<x-layouts.publico titulo="Conversación">
    <div class="mx-auto max-w-3xl px-4 py-6 sm:px-6">
        <a href="{{ route('mensajes.index') }}" class="text-sm text-verde-700 hover:underline">← Volver a los mensajes</a>

        <h1 class="mb-4 mt-2 text-xl font-bold text-gray-900">
            {{ $conversacion->revendedor_id === auth()->id() ? $conversacion->feriante?->nombre : $conversacion->revendedor?->name }}
        </h1>

        <div class="space-y-3">
            @foreach ($conversacion->mensajes as $mensaje)
                @php $propio = $mensaje->user_id === auth()->id(); @endphp
                <div @class(['flex', 'justify-end' => $propio])>
                    <div @class(['max-w-[85%] rounded-lg px-4 py-2 shadow-sm', 'bg-verde-600 text-white' => $propio, 'bg-white ring-1 ring-gray-200' => ! $propio])>
                        <p class="whitespace-pre-line text-sm">{{ $mensaje->cuerpo }}</p>
                        <p @class(['mt-1 text-xs', 'text-verde-100' => $propio, 'text-gray-500' => ! $propio])>
                            {{ $mensaje->created_at->format('d/m H:i') }}
                            @if ($propio && $mensaje->leido_at) · Leído @endif
                        </p>
                    </div>
                </div>
            @endforeach
        </div>

        <form method="POST" action="{{ route('mensajes.responder', $conversacion) }}" class="mt-6">
            @csrf
            <x-campo nombre="cuerpo" etiqueta="Tu respuesta" tipo="textarea" required maxlength="1000" />
            <x-boton>Enviar</x-boton>
        </form>
    </div>
</x-layouts.publico>
