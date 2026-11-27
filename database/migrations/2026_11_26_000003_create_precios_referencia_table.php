<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * PAI-152: precio tope semanal que sugiere el PAIPPA para cada producto
 * (RF-007, RN-04, RN-16). Surge del relevamiento (PAIPPA 7): cada semana el
 * PAIPPA promedia los precios de supermercados y verdulerías y fija un tope.
 * Se guarda uno por producto y por semana, así queda el historial de topes.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('precios_referencia', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos')->cascadeOnDelete();
            $table->decimal('precio_tope', 10, 2);
            // El lunes de la semana a la que corresponde el tope.
            $table->date('semana');
            // El admin que lo cargó.
            $table->foreignId('cargado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            // Un solo tope por producto y semana.
            $table->unique(['producto_id', 'semana']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('precios_referencia');
    }
};
