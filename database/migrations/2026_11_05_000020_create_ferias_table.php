<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ferias', function (Blueprint $table) {
            $table->id();
            // Único solo entre las activas: se valida con Rule::unique()->withoutTrashed().
            $table->string('nombre', 100)->index();
            $table->text('descripcion')->nullable();
            $table->string('dias_horario', 150);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ferias');
    }
};
