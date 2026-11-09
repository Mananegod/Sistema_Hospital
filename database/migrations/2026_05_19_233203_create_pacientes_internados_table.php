<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pacientes_internados', function (Blueprint $table) {
            $table->id();
            $table->string('tipo_documento', 5)->default('V');
            $table->string('cedula')->nullable();
            $table->string('cedula_tutor')->nullable();
            $table->string('nombre_apellido');
            $table->date('fecha_nacimiento');
            $table->integer('edad');
            $table->string('genero');
            $table->unsignedBigInteger('area_id');
            $table->string('diagnostico');
            $table->text('tratamiento')->nullable();
            $table->date('fecha_ingreso');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pacientes_internados');
    }
};