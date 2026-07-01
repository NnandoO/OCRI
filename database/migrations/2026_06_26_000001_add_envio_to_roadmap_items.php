<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roadmap_items', function (Blueprint $table) {
            $table->string('envio_tipo')->nullable()->after('order');
            $table->string('numero_expediente')->nullable()->after('envio_tipo');
        });
    }

    public function down(): void
    {
        Schema::table('roadmap_items', function (Blueprint $table) {
            $table->dropColumn(['envio_tipo', 'numero_expediente']);
        });
    }
};
