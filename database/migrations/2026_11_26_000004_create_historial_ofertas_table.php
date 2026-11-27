<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * PAI-152: historial de cada oferta (RN-15). Cada alta o cambio de precio,
 * cantidad o vigencia queda registrado con la fecha y el autor. Lo usan las
 * estadísticas de lo ofrecido (RF-017). Solo tiene created_at: un registro del
 * historial no se modifica.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('historial_ofertas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('oferta_id')->constrained('ofertas')->cascadeOnDelete();
            // Quién hizo el cambio; nulo si fue un proceso automático (seeder).
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('accion', 20);
            // Los valores de la oferta después del cambio.
            $table->decimal('precio', 10, 2);
            $table->decimal('cantidad_disponible', 10, 2);
            $table->date('vigente_hasta');
            $table->timestamp('created_at')->useCurrent();

            $table->index(['oferta_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historial_ofertas');
    }
};
