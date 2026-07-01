<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agreements', function (Blueprint $table) {
            $table->string('dictamen_path')->nullable()->after('situation');
            $table->string('dictamen_original_name')->nullable()->after('dictamen_path');
        });
    }

    public function down(): void
    {
        Schema::table('agreements', function (Blueprint $table) {
            $table->dropColumn(['dictamen_path', 'dictamen_original_name']);
        });
    }
};
