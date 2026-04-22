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
    Schema::table('personal', function (Blueprint $table) {
        $table->boolean('activo')->default(true);
    });
    }

    /**
     * Reverse the migrations.
     */
    /**
 * Reverse the migrations.
 */
    public function down(): void
    {
        Schema::table('personal', function (Blueprint $table) {
        $table->dropColumn('activo'); // Añade esto para un rollback limpio
        });
    }
};
