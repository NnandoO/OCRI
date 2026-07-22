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
        Schema::create('agreement_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agreement_id')->constrained()->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->date('date');
            
            // Archivo 1: Oficio/Informe
            $table->string('oficio_path')->nullable();
            $table->string('oficio_original_name')->nullable();
            
            // Archivo 2: Respuesta
            $table->string('respuesta_path')->nullable();
            $table->string('respuesta_original_name')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agreement_reports');
    }
};
