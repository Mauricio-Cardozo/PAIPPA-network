<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Consultas de productos para las estadísticas (HU15).
     * Se cuenta como máximo una consulta por producto, origen, visitante y día.
     * «visitante» nunca guarda el id de sesión crudo: guarda «user:{id}» o un hash.
     */
    public function up(): void
    {
        Schema::create('consultas_producto', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('visitante', 80);
            $table->string('origen', 20); // 'busqueda' o 'whatsapp'
            $table->date('fecha')->index();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['producto_id', 'origen', 'visitante', 'fecha'], 'consultas_producto_unica_por_dia');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consultas_producto');
    }
};
