<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feriantes', function (Blueprint $table) {
            $table->id();
            // Cuenta opcional del feriante (para la mensajería).
            $table->foreignId('user_id')->nullable()->unique()->constrained('users')->nullOnDelete();
            $table->foreignId('feria_id')->constrained('ferias')->restrictOnDelete();
            $table->string('nombre', 120);
            // Solo dígitos, formato 549XXXXXXXXXX.
            $table->string('telefono_whatsapp', 15);
            $table->text('descripcion')->nullable();
            // Nulo = pendiente de validar. Solo se publica si tiene fecha.
            $table->timestamp('validado_at')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feriantes');
    }
};
