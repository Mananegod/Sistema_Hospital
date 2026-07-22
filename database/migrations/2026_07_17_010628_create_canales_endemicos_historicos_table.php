<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('canales_endemicos_historicos', function (Blueprint $table) {
            $table->id();
            $table->string('patologia_cie10');
            $table->integer('ano');
            $table->integer('semana'); // 1 a 52
            $table->integer('exito')->default(0);      // Límite zona Éxito (Q1)
            $table->integer('seguridad')->default(0);  // Límite zona Seguridad (Q2)
            $table->integer('alerta')->default(0);     // Límite zona Alerta (Q3)
            $table->integer('epidemia')->default(0);   // Límite zona Epidemia
            $table->integer('actual')->default(0);     // Casos reales registrados ese año (opcional)
            $table->timestamps();

            // Índice para acelerar las búsquedas del gráfico
            $table->unique(['patologia_cie10', 'ano', 'semana'], 'idx_canal_unico');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('canales_endemicos_historicos');
    }
};