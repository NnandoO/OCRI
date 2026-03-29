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
        Schema::create('agreements', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    $table->string('resolution_number')->unique();
    $table->foreignId('institution_id')->constrained()->onDelete('cascade');
    $table->foreignId('agreement_type_id')->constrained();
    $table->date('start_date');
    $table->date('end_date');
    $table->string('status')->default('Vigente'); // Vigente, Por Vencer, Vencido
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agreements');
    }
};
