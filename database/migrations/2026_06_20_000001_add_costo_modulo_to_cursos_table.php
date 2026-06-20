<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Agrega el total de módulos y el costo por módulo (cuota) calculado
     * automáticamente a partir del Costo Total de Estudios y el tipo de programa.
     */
    public function up(): void
    {
        Schema::table('cursos', function (Blueprint $table) {
            $table->integer('total_modulos')->default(0)->after('nro_modulos_maestria');
            $table->decimal('costo_modulo', 10, 2)->default(0)->after('total_modulos');
        });
    }

    public function down(): void
    {
        Schema::table('cursos', function (Blueprint $table) {
            $table->dropColumn(['total_modulos', 'costo_modulo']);
        });
    }
};
