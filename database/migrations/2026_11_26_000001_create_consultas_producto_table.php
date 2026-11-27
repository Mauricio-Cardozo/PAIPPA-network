<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * HU15: consultas por producto, para las estadísticas de uso.
 * Se cuenta como mucho una consulta por producto, origen, visitante y día.
 * No se guarda el id de sesión crudo: para los invitados, solo su hash.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consultas_producto', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            // «user:{id}» para los usuarios o el hash sha256 de la sesión (64 caracteres).
            $table->string('visitante', 80);
            $table->string('origen', 20);
            $table->date('fecha');
            $table->timestamp('created_at')->useCurrent();

            // Evita contar dos veces la misma consulta en el mismo día.
            $table->unique(['producto_id', 'origen', 'visitante', 'fecha']);
            // El panel de estadísticas filtra por rango de fechas.
            $table->index(['fecha', 'producto_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consultas_producto');
    }
};
