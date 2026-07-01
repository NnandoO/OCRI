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
            $table->index('status');
            $table->index('start_date');
            $table->index('end_date');
        });

        Schema::table('institutions', function (Blueprint $table) {
            $table->index('name');
            $table->index('country');
            $table->index('type');
        });

        Schema::table('oficios', function (Blueprint $table) {
            $table->index('oficio_number');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::table('agreements', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['start_date']);
            $table->dropIndex(['end_date']);
        });

        Schema::table('institutions', function (Blueprint $table) {
            $table->dropIndex(['name']);
            $table->dropIndex(['country']);
            $table->dropIndex(['type']);
        });

        Schema::table('oficios', function (Blueprint $table) {
            $table->dropIndex(['oficio_number']);
            $table->dropIndex(['created_at']);
        });
    }
};
