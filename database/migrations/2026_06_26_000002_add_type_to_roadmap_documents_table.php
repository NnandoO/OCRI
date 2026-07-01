<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roadmap_documents', function (Blueprint $table) {
            $table->string('type')->default('entrada')->after('original_name');
        });
    }

    public function down(): void
    {
        Schema::table('roadmap_documents', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
