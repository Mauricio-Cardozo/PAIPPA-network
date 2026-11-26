<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * PAI-152: parámetros del sistema que el PAIPPA puede cambiar sin tocar el
 * código, como clave y valor. Por ejemplo, el WhatsApp del área de
 * Comercialización, al que se deriva el contacto cuando el feriante no
 * autoriza mostrar su número (RN-06).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('configuracion', function (Blueprint $table) {
            $table->id();
            $table->string('clave', 60)->unique();
            $table->text('valor')->nullable();
            $table->string('descripcion', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('configuracion');
    }
};
