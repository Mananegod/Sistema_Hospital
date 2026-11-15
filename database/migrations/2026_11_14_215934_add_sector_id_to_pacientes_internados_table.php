<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pacientes_internados', function (Blueprint $table) {
            $table->unsignedBigInteger('sector_id')->nullable()->after('area_id');
        });
    }

    public function down(): void
    {
        Schema::table('pacientes_internados', function (Blueprint $table) {
            $table->dropColumn('sector_id');
        });
    }
};