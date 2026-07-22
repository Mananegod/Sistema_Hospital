<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Creamos la tabla personal de forma independiente
        Schema::create('personal', function (Blueprint $table) {
            $table->id();
            $table->string('cedula')->unique();
            $table->string('nombres');
            $table->string('apellidos');
            $table->string('cargo'); // Ej: Médico, Enfermero, Administrativo
            $table->string('tipo_usuario')->default('Usuario'); // Admin o Usuario
            $table->string('especialidad')->nullable(); 
            $table->string('turno'); // Mañana, Tarde, Noche
            $table->string('telefono');
            $table->boolean('activo')->default(true);
            $table->softDeletes(); // Requerido por SoftDeletes de Eloquent
            $table->timestamps();
        });

        // 2. Ahora que AMBAS tablas existen, enlazamos la clave foránea en usuarios
        Schema::table('usuarios', function (Blueprint $table) {
            $table->foreign('personal_id')
                  ->references('id')
                  ->on('personal')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->dropForeign(['personal_id']);
        });

        Schema::dropIfExists('personal');
    }
};