<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Agrega el rol y los datos de bloqueo a los usuarios.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('rol')->default('revendedor')->index()->after('email');
            $table->string('telefono', 20)->nullable()->after('rol');
            $table->timestamp('bloqueado_at')->nullable();
            $table->foreignId('bloqueado_por')->nullable()->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('bloqueado_por');
            $table->dropIndex(['rol']);
            $table->dropColumn(['rol', 'telefono', 'bloqueado_at']);
        });
    }
};
