<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * PAI-138: según la ERS final, las ofertas ya no se miden en cajas sino en
 * la unidad del producto (kilo, atado, bolsa...). Se renombran las columnas
 * y se agregan las de promoción, entrega y cierre (diccionario de datos 2.4.11).
 * No se edita la migración original porque ya corrió en todas las bases.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ofertas', function (Blueprint $table) {
            $table->renameColumn('precio_caja', 'precio');
            $table->renameColumn('cajas_disponibles', 'cantidad_disponible');
            $table->renameColumn('vigente_desde', 'disponible_desde');
        });

        Schema::table('ofertas', function (Blueprint $table) {
            // Ahora admite decimales: por ejemplo, 2,5 kilos.
            $table->decimal('cantidad_disponible', 10, 2)->default(0)->change();
            $table->boolean('promocion')->default(false);
            $table->boolean('entrega_domicilio')->default(false);
            $table->decimal('cantidad_vendida', 10, 2)->nullable();
            $table->timestamp('cerrada_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('ofertas', function (Blueprint $table) {
            $table->dropColumn(['promocion', 'entrega_domicilio', 'cantidad_vendida', 'cerrada_at']);
            $table->unsignedInteger('cantidad_disponible')->default(0)->change();
        });

        Schema::table('ofertas', function (Blueprint $table) {
            $table->renameColumn('precio', 'precio_caja');
            $table->renameColumn('cantidad_disponible', 'cajas_disponibles');
            $table->renameColumn('disponible_desde', 'vigente_desde');
        });
    }
};
