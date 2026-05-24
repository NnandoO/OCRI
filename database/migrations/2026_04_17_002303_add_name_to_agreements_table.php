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
    Schema::table('agreements', function (Blueprint $table) {
        // Añadimos la columna 'name' después de 'title'
        $table->text('name')->nullable()->after('title');
    });
}

public function down(): void
{
    Schema::table('agreements', function (Blueprint $table) {
        $table->dropColumn('name');
    });
}
};
