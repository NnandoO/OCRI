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
        Schema::table('practicantes', function (Blueprint $table) {
            $table->string('dni', 8)->nullable()->unique()->after('nombre');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('practicantes', function (Blueprint $table) {
            $table->dropColumn('dni');
        });
    }
};
