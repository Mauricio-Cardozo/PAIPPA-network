<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * PAI-152: medios de pago que acepta cada feriante (RF-006, RF-011).
 * Surge del relevamiento (PAIPPA 15): el perfil público muestra cómo se paga.
 * Un feriante acepta varios medios y un medio lo aceptan varios feriantes:
 * relación de muchos a muchos con la tabla intermedia feriante_medio_pago.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medios_pago', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 50)->unique();
            $table->timestamps();
        });

        Schema::create('feriante_medio_pago', function (Blueprint $table) {
            // Si se borra el feriante o el medio, se borra el vínculo: es un dato derivado.
            $table->foreignId('feriante_id')->constrained('feriantes')->cascadeOnDelete();
            $table->foreignId('medio_pago_id')->constrained('medios_pago')->cascadeOnDelete();

            // Un feriante no puede tener el mismo medio dos veces.
            $table->primary(['feriante_id', 'medio_pago_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feriante_medio_pago');
        Schema::dropIfExists('medios_pago');
    }
};
