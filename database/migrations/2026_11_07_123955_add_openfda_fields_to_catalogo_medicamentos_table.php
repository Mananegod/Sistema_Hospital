<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('catalogo_medicamentos', function (Blueprint $table) {
            if (!Schema::hasColumn('catalogo_medicamentos', 'indicaciones')) {
                $table->text('indicaciones')->nullable();
            }
            if (!Schema::hasColumn('catalogo_medicamentos', 'dosificacion')) {
                $table->text('dosificacion')->nullable();
            }
            if (!Schema::hasColumn('catalogo_medicamentos', 'contraindicaciones')) {
                $table->text('contraindicaciones')->nullable();
            }
            if (!Schema::hasColumn('catalogo_medicamentos', 'advertencias')) {
                $table->text('advertencias')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('catalogo_medicamentos', function (Blueprint $table) {
            $table->dropColumn(['indicaciones', 'dosificacion', 'contraindicaciones', 'advertencias']);
        });
    }
};