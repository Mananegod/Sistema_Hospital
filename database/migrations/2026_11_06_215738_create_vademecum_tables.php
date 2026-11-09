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
        // 1. Tabla de Fabricantes / Laboratorios
        Schema::create('laboratorios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->unique();
            $table->string('pais', 100)->nullable();
            $table->timestamps();
        });

        // 2. Formas Farmacéuticas (Tableta, Solución Inyectable, Jarabe, etc.)
        Schema::create('formas_farmaceuticas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 150)->unique();
            $table->timestamps();
        });

        // 3. Catálogo Principal Normalizado (Ficha farmacológica)
        Schema::create('catalogo_medicamentos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_generico');
            $table->string('nombre_comercial')->nullable();
            $table->string('concentracion', 120)->nullable();
            $table->string('codigo_atc', 50)->nullable();
            
            $table->foreignId('forma_farmaceutica_id')
                  ->nullable()
                  ->constrained('formas_farmaceuticas')
                  ->nullOnDelete();

            $table->foreignId('laboratorio_id')
                  ->nullable()
                  ->constrained('laboratorios')
                  ->nullOnDelete();

            $table->string('condicion_dispensa', 100)->nullable(); // Ej: Bajo receta médica
            $table->text('almacenamiento')->nullable();          // Ej: Mantener entre 2°C y 8°C
            
            // Campos clínicos integrados para la ficha farmacológica
            $table->text('indicaciones')->nullable();
            $table->text('dosificacion')->nullable();
            $table->text('contraindicaciones')->nullable();
            $table->text('advertencias')->nullable();

            $table->string('registro_sanitario', 120)->nullable();
            $table->enum('estado', ['activo', 'inactivo', 'pendiente'])->default('pendiente');
            
            $table->timestamps();

            // Índices para agilizar búsquedas por texto
            $table->index('nombre_generico');
            $table->index('nombre_comercial');
        });

        // 4. Presentaciones Comerciables del Catálogo
        Schema::create('presentaciones_catalogo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('catalogo_medicamento_id')
                  ->constrained('catalogo_medicamentos')
                  ->cascadeOnDelete();

            $table->string('descripcion'); // Ej: Caja de 20 tabletas / Ampolla 5ml
            $table->decimal('cantidad', 12, 3)->nullable();
            $table->string('unidad', 50)->nullable(); // Ej: mg, ml, tabletas
            $table->string('codigo_barras', 100)->nullable();
            $table->timestamps();

            $table->index('codigo_barras');
        });

        // 5. Registro de Proveedores Externos de Vademécum
        Schema::create('fuentes_vademecum', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100)->unique(); // Ej: VIDAL, Mi Vademécum
            $table->enum('tipo', ['api', 'archivo', 'manual'])->default('api');
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        // 6. Relación N:M entre Medicamentos de tu Catálogo e Identificadores de APIs Externas
        Schema::create('medicamento_fuentes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('catalogo_medicamento_id')
                  ->constrained('catalogo_medicamentos')
                  ->cascadeOnDelete();

            $table->foreignId('fuente_id')
                  ->constrained('fuentes_vademecum')
                  ->cascadeOnDelete();

            $table->string('identificador_externo', 150);
            $table->timestamp('fecha_consulta')->nullable();
            $table->string('version_fuente', 100)->nullable();
            $table->timestamps();

            // Evita duplicar la misma referencia de un mismo proveedor
            $table->unique(['fuente_id', 'identificador_externo'], 'uq_fuente_externa');
        });

        // 7. Historial de Sincronización y Registro de Consultas API
        Schema::create('sincronizaciones_vademecum', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fuente_id')
                  ->constrained('fuentes_vademecum')
                  ->cascadeOnDelete();

            $table->string('tipo_operacion', 80); // Ej: consulta_api, carga_archivo, sync_masiva
            $table->enum('estado', ['iniciada', 'correcta', 'fallida', 'parcial'])->default('iniciada');
            $table->integer('registros_procesados')->default(0);
            $table->text('detalle')->nullable();
            $table->timestamp('fecha_inicio')->useCurrent();
            $table->timestamp('fecha_fin')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sincronizaciones_vademecum');
        Schema::dropIfExists('medicamento_fuentes');
        Schema::dropIfExists('fuentes_vademecum');
        Schema::dropIfExists('presentaciones_catalogo');
        Schema::dropIfExists('catalogo_medicamentos');
        Schema::dropIfExists('formas_farmaceuticas');
        Schema::dropIfExists('laboratorios');
    }
};