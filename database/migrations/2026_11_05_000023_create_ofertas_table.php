<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ofertas', function (Blueprint $table) {
            $table->id();
            // Las ofertas son historial: no se borran sus padres.
            $table->foreignId('feriante_id')->constrained('feriantes')->restrictOnDelete();
            $table->foreignId('producto_id')->constrained('productos')->restrictOnDelete();
            $table->foreignId('punto_retiro_id')->constrained('puntos_retiro')->restrictOnDelete();
            $table->decimal('precio_caja', 10, 2);
            $table->unsignedInteger('cajas_disponibles')->default(0);
            $table->date('vigente_desde');
            $table->date('vigente_hasta');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ofertas');
    }
};
