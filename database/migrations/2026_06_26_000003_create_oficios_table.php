<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('oficios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agreement_id')->constrained()->onDelete('cascade');
            $table->foreignId('roadmap_item_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('area_name');
            $table->string('directed_to');
            $table->string('oficio_number');
            $table->string('file_path')->nullable();
            $table->string('file_original_name')->nullable();
            $table->enum('type', ['opinion', 'final'])->default('opinion');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('oficios');
    }
};
