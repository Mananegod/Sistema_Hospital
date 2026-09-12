<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('insumos_medicos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_insumo');
            $table->string('presentacion')->nullable();
            $table->integer('cantidad_stock')->default(0);
            $table->integer('stock_minimo')->default(0);
            $table->string('tipo_insumo')->nullable();
            $table->string('codigo_lote')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('insumos_medicos');
    }
};
