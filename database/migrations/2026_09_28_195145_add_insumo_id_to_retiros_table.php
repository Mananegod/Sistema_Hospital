<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('retiros', function (Blueprint $table) {
            // Permitir que medicamento_id sea opcional cuando se retira un insumo
            $table->unsignedBigInteger('medicamento_id')->nullable()->change();

            // Agregar la columna insumo_id si no existe
            if (!Schema::hasColumn('retiros', 'insumo_id')) {
                $table->unsignedBigInteger('insumo_id')->nullable()->after('medicamento_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('retiros', function (Blueprint $table) {
            if (Schema::hasColumn('retiros', 'insumo_id')) {
                $table->dropColumn('insumo_id');
            }
        });
    }
};