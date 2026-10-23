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
    Schema::create('bajas_inventario', function (Blueprint $table) {
        $table->id();
        $table->string('tipo_item'); // 'medicamento' o 'insumo'
        $table->string('nombre_item');
        $table->string('codigo_lote')->nullable();
        $table->date('fecha_vencimiento')->nullable();
        $table->integer('cantidad');
        $table->string('motivo');
        $table->unsignedBigInteger('area_id')->nullable(); // Opcional, si aplica
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bajas_inventario');
    }
};
