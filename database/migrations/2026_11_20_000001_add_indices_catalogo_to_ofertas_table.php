<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Índices para la consulta del catálogo (PAI-65), según lo medido en
     * docs/pruebas/rendimiento.md:
     * - vigencia + stock: el filtro que se aplica siempre (scope vigentes).
     * - updated_at: el orden del listado («lo último actualizado primero»).
     * Las claves foráneas (feriante_id, producto_id, punto_retiro_id) ya
     * tienen su índice, y feriantes.validado_at también.
     */
    public function up(): void
    {
        Schema::table('ofertas', function (Blueprint $table) {
            $table->index(['vigente_hasta', 'vigente_desde', 'cajas_disponibles'], 'ofertas_vigencia_stock_index');
            $table->index('updated_at');
        });
    }

    public function down(): void
    {
        Schema::table('ofertas', function (Blueprint $table) {
            $table->dropIndex('ofertas_vigencia_stock_index');
            $table->dropIndex(['updated_at']);
        });
    }
};
