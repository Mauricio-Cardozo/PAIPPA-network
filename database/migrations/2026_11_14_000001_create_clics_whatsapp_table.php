<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Registro de los clics en «Contactar por WhatsApp».
     * Por la Ley 25.326 no se guarda el texto del mensaje, y se borra a los 12 meses.
     * Un clic registra interés, no una compra.
     */
    public function up(): void
    {
        Schema::create('clics_whatsapp', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('feriante_id')->constrained('feriantes')->cascadeOnDelete();
            $table->foreignId('oferta_id')->nullable()->constrained('ofertas')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clics_whatsapp');
    }
};
