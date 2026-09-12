<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Crear la tabla independiente de Lotes / Trazabilidad
        Schema::create('lotes', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_lote')->unique();
            $table->date('fecha_vencimiento')->nullable();
            $table->date('fecha_ingreso')->nullable();
            $table->string('proveedor')->nullable();
            // Permite registrar si un lote fue reportado como defectuoso/tóxico/vencido
            $table->enum('estado', ['Disponible', 'En Cuarentena', 'Defectuoso / Retirado'])->default('Disponible');
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });

        // 2. Relacionar con la tabla 'medicamentos'
        Schema::table('medicamentos', function (Blueprint $table) {
            $table->foreignId('lote_id')->nullable()->after('tipo_insumo')->constrained('lotes')->nullOnDelete();
        });

        // 3. Relacionar con la tabla 'insumos_medicos'
        Schema::table('insumos_medicos', function (Blueprint $table) {
            $table->foreignId('lote_id')->nullable()->after('tipo_insumo')->constrained('lotes')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('insumos_medicos', function (Blueprint $table) {
            $table->dropForeign(['lote_id']);
            $table->dropColumn('lote_id');
        });

        Schema::table('medicamentos', function (Blueprint $table) {
            $table->dropForeign(['lote_id']);
            $table->dropColumn('lote_id');
        });

        Schema::dropIfExists('lotes');
    }
};