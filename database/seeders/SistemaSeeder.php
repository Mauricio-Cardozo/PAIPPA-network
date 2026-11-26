<?php

namespace Database\Seeders;

use App\Models\Configuracion;
use App\Models\Feriante;
use App\Models\MedioPago;
use Illuminate\Database\Seeder;

/**
 * PAI-152: medios de pago y parámetros del sistema.
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

        // Parámetros: el valor real lo carga el PAIPPA. No se ponen teléfonos
        // reales en el código.
        Configuracion::firstOrCreate(
            ['clave' => 'comercializacion_whatsapp'],
            [
                'valor' => null,
                'descripcion' => 'WhatsApp del área de Comercialización del PAIPPA (549 + código de área + número). Se usa cuando el feriante no autoriza mostrar su número (RN-06).',
            ],
        );
    }
}
