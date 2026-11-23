<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')->where('rol', 'revendedor')->update(['rol' => 'comprador']);

        Schema::table('users', function (Blueprint $table) {
            $table->string('rol')->default('comprador')->change();
        });
    }

    public function down(): void
    {
        DB::table('users')->where('rol', 'comprador')->update(['rol' => 'revendedor']);

        Schema::table('users', function (Blueprint $table) {
            $table->string('rol')->default('revendedor')->change();
        });
    }
};
