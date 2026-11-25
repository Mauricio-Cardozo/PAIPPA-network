<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * PAI-49: datos del feriante que salieron del relevamiento (diccionario 2.4.4).
 * - procedencia: localidad o colonia de la producción (RF-006, PAIPPA 14).
 * - autoriza_whatsapp: si autoriza mostrar su número (RN-06, feriantes 13).
 * - validado_por: el administrador que lo validó (RN-01).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('feriantes', function (Blueprint $table) {
            // Nula en la base porque los feriantes cargados antes no la tenían;
            // el formulario la pide obligatoria.
            $table->string('procedencia', 150)->nullable()->after('nombre');
            // Por defecto NO se muestra el número hasta que el feriante lo autorice.
            $table->boolean('autoriza_whatsapp')->default(false)->after('telefono_whatsapp');
            $table->foreignId('validado_por')->nullable()->after('validado_at')
                ->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('feriantes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('validado_por');
            $table->dropColumn(['procedencia', 'autoriza_whatsapp']);
        });
    }
};
