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
        // 1. ESTO ES LO NUEVO: Borramos la tabla "a medias" si es que existe
        Schema::dropIfExists('roadmap_documents');

        // 2. Y luego la creamos desde cero correctamente
        Schema::create('roadmap_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('roadmap_item_id')->constrained('roadmap_items')->onDelete('cascade');
            $table->string('file_path');
            $table->string('original_name');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roadmap_documents');
    }
};