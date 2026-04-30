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
    // Catálogo General de Medicamentos
    Schema::create('medicamentos', function (Blueprint $table) {
        $table->id();
        $table->string('nombre');
        $table->string('presentacion')->nullable();
        $table->string('categoria')->nullable();
        $table->integer('stock_minimo')->default(10);
        $table->string('codigo_lote');
        $table->string('nombre_medicamento');
        $table->integer('cantidad_stock');
        $table->string('area_destino');
        $table->date('fecha_vencimiento');
        $table->string('status_disponibilidad');
        $table->timestamps();
    });

    // Áreas del Hospital (Emergencia, Quirófano, etc.)
    Schema::create('areas', function (Blueprint $table) {
        $table->id();
        $table->string('nombre_area');
        $table->timestamps();
    });

    // Inventario por Área
    Schema::create('inventarios', function (Blueprint $table) {
        $table->id();
        $table->foreignId('medicamento_id')->constrained('medicamentos');
        $table->foreignId('area_id')->constrained('areas');
        $table->integer('stock_actual')->default(0);
        $table->timestamps();
    });

    // Registro de Retiros (Auditoría)
    Schema::create('movimientos', function (Blueprint $table) {
        $table->id();
        $table->foreignId('medicamento_id')->constrained('medicamentos');
        $table->foreignId('area_id')->constrained('areas');
        $table->integer('cantidad');
        $table->string('tipo')->default('retiro');
        $table->string('responsable')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hospital_tables');
    }
};
