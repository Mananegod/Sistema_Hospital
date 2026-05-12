<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up() {
    Schema::create('pacientes', function (Blueprint $table) {
        $table->id();
        $table->string('cedula')->unique();
        $table->string('nombres');
        $table->string('apellidos');
        $table->integer('edad');
        $table->string('servicio');
        $table->string('servicio_cod')->nullable();
        $table->string('sala_cama')->nullable();
        $table->string('n_comprobante')->nullable();
        $table->date('fecha_ingreso');
        $table->text('diagnostico'); // Dx
        $table->text('tratamiento');
        $table->boolean('de_alta')->default(false);
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pacientes');
    }
};
