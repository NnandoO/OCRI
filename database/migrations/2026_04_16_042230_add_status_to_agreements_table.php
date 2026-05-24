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
        // Añadimos la columna status después del campo 'title' (opcional el 'after')
        // El valor default asegura que los convenios antiguos no rompan el mapa
        $table->string('status')->default('Formulación')->after('title');
    });
}

public function down(): void
{
    Schema::table('agreements', function (Blueprint $table) {
        // Siempre es buena práctica poder revertir el cambio
        $table->dropColumn('status');
        });
    }
};