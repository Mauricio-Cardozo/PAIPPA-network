<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * PAI-75: registro de los clics en «Contactar por WhatsApp» (diccionario 2.4.13).
 * Guarda quién, a qué feriante, por qué oferta y cuándo. No guarda el texto del
 * mensaje (RN-10, Ley 25.326) y los registros se borran a los 12 meses.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clics_whatsapp', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('feriante_id')->constrained('feriantes')->cascadeOnDelete();
            $table->foreignId('oferta_id')->nullable()->constrained('ofertas')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();

            // Contactos por feriante y por fecha, para las estadísticas (RF-017).
            $table->index(['feriante_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clics_whatsapp');
    }
};
