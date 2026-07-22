<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('practicantes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->unique();
            $table->timestamps();
        });

        Schema::table('asistencia', function (Blueprint $table) {
            $table->unsignedBigInteger('practicante_id')->nullable()->after('id');
        });

        // Migrate data
        $asistencias = DB::table('asistencia')->get();
        foreach ($asistencias as $asistencia) {
            if (!empty($asistencia->nombre)) {
                $practicante = DB::table('practicantes')->where('nombre', $asistencia->nombre)->first();
                if (!$practicante) {
                    $practicanteId = DB::table('practicantes')->insertGetId([
                        'nombre' => $asistencia->nombre,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } else {
                    $practicanteId = $practicante->id;
                }
                
                DB::table('asistencia')
                    ->where('id', $asistencia->id)
                    ->update(['practicante_id' => $practicanteId]);
            }
        }

        Schema::table('asistencia', function (Blueprint $table) {
            $table->unsignedBigInteger('practicante_id')->nullable(false)->change();
            $table->foreign('practicante_id')->references('id')->on('practicantes')->cascadeOnDelete();
            
            // Drop old column
            $table->dropColumn('nombre');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asistencia', function (Blueprint $table) {
            $table->string('nombre')->nullable()->after('id');
        });

        // Restore data
        $asistencias = DB::table('asistencia')->get();
        foreach ($asistencias as $asistencia) {
            if ($asistencia->practicante_id) {
                $practicante = DB::table('practicantes')->where('id', $asistencia->practicante_id)->first();
                if ($practicante) {
                    DB::table('asistencia')
                        ->where('id', $asistencia->id)
                        ->update(['nombre' => $practicante->nombre]);
                }
            }
        }

        Schema::table('asistencia', function (Blueprint $table) {
            $table->dropForeign(['practicante_id']);
            $table->dropColumn('practicante_id');
        });

        Schema::dropIfExists('practicantes');
    }
};
