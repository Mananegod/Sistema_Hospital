<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('casos_epidemiologicos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_paciente');
            $table->string('cedula_paciente')->nullable();
            $table->string('patologia_cie10');
            $table->string('sector_procedencia');
            $table->date('fecha_sintomas');
            $table->enum('estado_caso', ['SOSPECHOSO', 'PROBABLE', 'CONFIRMADO'])->default('SOSPECHOSO');
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('casos_epidemiologicos');
    }
};