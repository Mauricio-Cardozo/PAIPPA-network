<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('puntos_retiro', function (Blueprint $table) {
            $table->id();
            $table->foreignId('feria_id')->constrained('ferias')->restrictOnDelete();
            $table->string('nombre', 100);
            $table->string('direccion', 200);
            $table->string('horario', 150);
            $table->decimal('latitud', 10, 7);
            $table->decimal('longitud', 10, 7);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('puntos_retiro');
    }
};
