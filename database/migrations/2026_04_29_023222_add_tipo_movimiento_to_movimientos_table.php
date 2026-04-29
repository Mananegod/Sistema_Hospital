<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('movimientos', function (Blueprint $table) {
        // Agregamos la columna para distinguir entre ENTRADA y SALIDA
        $table->string('tipo_movimiento', 20)->after('cantidad')->nullable();
    });
}

public function down()
{
    Schema::table('movimientos', function (Blueprint $table) {
        $table->dropColumn('tipo_movimiento');
    });
}
};
