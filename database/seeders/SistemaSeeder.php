<?php

namespace Database\Seeders;

use App\Models\Configuracion;
use App\Models\Feriante;
use App\Models\MedioPago;
use Illuminate\Database\Seeder;

/**
 * PAI-152: medios de pago y parámetros del sistema.
 * El WhatsApp de Comercialización no va acá: está en el .env
 * (PAIPPA_WHATSAPP_COMERCIALIZACION, PAI-74).
 * Corre después de FeriaSeeder para asignarles medios de pago a los feriantes.
 */
class SistemaSeeder extends Seeder
{
    public function run(): void
    {
        // Medios de pago de ejemplo: el relevamiento pide mostrarlos (PAIPPA 15),
        // pero no dice cuáles; estos son los habituales en las ferias.
        $medios = collect(['Efectivo', 'Transferencia', 'Mercado Pago'])
            ->map(fn (string $nombre) => MedioPago::firstOrCreate(['nombre' => $nombre]));

        // Todos aceptan efectivo; algunos, además, transferencia o Mercado Pago.
        foreach (Feriante::all() as $i => $feriante) {
            $feriante->mediosPago()->syncWithoutDetaching(
                $medios->take(1 + $i % 3)->pluck('id')
            );
        }

        // RN-05: la oferta vence a los 7 días sin actualizarse.
        Configuracion::firstOrCreate(
            ['clave' => 'dias_vigencia_oferta'],
            ['valor' => '7', 'descripcion' => 'Días sin actualizar después de los que vence una oferta (RN-05).'],
        );
    }
}
