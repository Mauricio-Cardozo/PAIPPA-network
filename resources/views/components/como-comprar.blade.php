{{--
    PAI-151 (RF-009, pantalla P07): cómo comprar, arriba del catálogo.
    Texto simple y letra grande, pensado también para usuarios mayores.
    $ferias: ferias fijas y las itinerantes que todavía no pasaron.
    $whatsapp: número del área de Comercialización del PAIPPA, o null si no está cargado.
--}}
@props(['ferias', 'whatsapp' => null])

<section class="mb-6 rounded-2xl border border-[#DDE4D3] bg-white p-5 sm:p-6" aria-labelledby="como-comprar">

    <h2 id="como-comprar" class="text-xl font-bold text-[#1F4D22]">
        ¿Cómo comprar?
    </h2>

    {{-- Los tres pasos --}}
    <ol class="mt-4 grid grid-cols-1 gap-4 text-base sm:grid-cols-3">
        <li class="flex gap-3">
            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-paippa-green text-lg font-bold text-white">1</span>
            <p><strong>Elegí la oferta</strong> que te interesa en el catálogo.</p>
        </li>
        <li class="flex gap-3">
            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-paippa-green text-lg font-bold text-white">2</span>
            <p><strong>Escribile por WhatsApp</strong> al feriante con el botón de la oferta.</p>
        </li>
        <li class="flex gap-3">
            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-paippa-green text-lg font-bold text-white">3</span>
            <p><strong>Retirá en la feria</strong> o en el punto de retiro que figura en la oferta.</p>
        </li>
    </ol>

    <p class="mt-4 text-base text-[#3D4A3E]">
        Algunos feriantes hacen entrega a domicilio: consultalo cuando le escribas.
    </p>

    {{-- Días y horarios: plegado para que el encabezado quede corto --}}
    <details class="mt-4 rounded-xl bg-[#F5F7F0] p-4">
        <summary class="cursor-pointer text-base font-semibold text-paippa-green">
            Días y horarios de las ferias
        </summary>

        @if ($ferias->isEmpty())
            <p class="mt-3 text-base text-[#3D4A3E]">Por ahora no hay ferias cargadas.</p>
        @else
            <ul class="mt-3 space-y-2 text-base">
                @foreach ($ferias as $feria)
                    <li>
                        <strong>{{ $feria->nombre }}</strong>
                        <span class="block text-[#3D4A3E]">
                            {{ $feria->cuando }}
                            @if ($feria->direccion)
                                · {{ $feria->direccion }}
                            @endif
                        </span>
                    </li>
                @endforeach
            </ul>
        @endif
    </details>

    {{-- Contacto del PAIPPA (área de Comercialización, RN-06) --}}
    <p class="mt-4 text-base text-[#3D4A3E]">
        ¿Tenés dudas?
        @if ($whatsapp)
            <a href="https://wa.me/{{ $whatsapp }}" target="_blank" rel="noopener"
               class="font-semibold text-paippa-green underline">Escribile al PAIPPA por WhatsApp</a>.
        @else
            Consultá en el PAIPPA.
        @endif
    </p>
</section>
