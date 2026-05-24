<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up() {
    Schema::create('roadmap_items', function (Blueprint $table) {
        $table->id();
        $table->foreignId('agreement_id')->constrained()->onDelete('cascade');
        $table->string('area_name'); // Ej: Asesoría Legal
        $table->boolean('is_completed')->default(false);
        $table->integer('order')->default(0);
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roadmap_items');
    }
};
