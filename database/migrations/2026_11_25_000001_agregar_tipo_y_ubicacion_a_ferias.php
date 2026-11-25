<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * PAI-149: ferias fijas e itinerantes, como en el diccionario de datos (2.4.2).
 * No se edita la migración original: se agregan las columnas nuevas, se pasa
 * dias_horario a dias y después se elimina dias_horario.
 *
 * Las columnas nuevas quedan nulas en la base porque las ferias cargadas antes
 * no tenían esos datos; el formulario (FeriaRequest) las pide obligatorias.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ferias', function (Blueprint $table) {
            $table->string('tipo', 20)->default('fija')->after('nombre');
            $table->string('direccion', 150)->nullable()->after('tipo');
            $table->string('barrio', 100)->nullable()->after('direccion');
            $table->string('dias', 50)->nullable()->after('barrio');
            $table->date('fecha')->nullable()->after('dias');
            $table->time('hora_inicio')->nullable()->after('fecha');
            $table->time('hora_fin')->nullable()->after('hora_inicio');
            $table->decimal('latitud', 10, 7)->nullable()->after('hora_fin');
            $table->decimal('longitud', 10, 7)->nullable()->after('latitud');
        });

        // Lo que estaba en dias_horario pasa a dias (recortado a 50 caracteres).
        DB::table('ferias')->update(['dias' => DB::raw('substr(dias_horario, 1, 50)')]);

        Schema::table('ferias', function (Blueprint $table) {
            $table->dropColumn('dias_horario');
        });
    }

    public function down(): void
    {
        Schema::table('ferias', function (Blueprint $table) {
            $table->string('dias_horario', 150)->default('')->after('descripcion');
        });

        DB::table('ferias')->update(['dias_horario' => DB::raw("coalesce(dias, '')")]);

        Schema::table('ferias', function (Blueprint $table) {
            $table->dropColumn([
                'tipo', 'direccion', 'barrio', 'dias', 'fecha',
                'hora_inicio', 'hora_fin', 'latitud', 'longitud',
            ]);
        });
    }
};
