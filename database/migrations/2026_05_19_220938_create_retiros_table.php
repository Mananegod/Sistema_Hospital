<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('retiros', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('medicamento_id')->nullable(); 
            $table->unsignedBigInteger('insumo_id')->nullable();
            $table->unsignedBigInteger('area_id');
            $table->integer('cantidad');
            $table->string('estado', 50)->default('pendiente');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('retiros');
    }
};