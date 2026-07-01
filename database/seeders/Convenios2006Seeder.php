<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Agreement;
use App\Models\Institution;
use App\Models\AgreementType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class Convenios2006Seeder extends Seeder
{
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        $tipos = [
            'marco' => AgreementType::firstOrCreate(['name' => 'Convenio Marco']),
            'especifico' => AgreementType::firstOrCreate(['name' => 'Convenio Específico']),
            'memorando' => AgreementType::firstOrCreate(['name' => 'Memorando de Entendimiento']),
            'adenda' => AgreementType::firstOrCreate(['name' => 'Adenda']),
        ];
        
        $datos = [
            ['013-2006', 'INTEJ', 'Empresa Internacional', 'CONVENIO MARCO DE COOPERACION CON LA ASOCIACION PROMOTORA DE INTEGRACION Y DE TURISMO EDUCATIVO JUVENIL', '2006-03-28', null, 'Internacional'],
        ];

        foreach ($datos as $fila) {
            $nombreInstitucion = strtoupper($fila[1]);
            $tipoEstandarizado = $fila[2];
            $pais = $fila[6];

            $institucion = Institution::firstOrCreate(
                ['name' => $nombreInstitucion],
                ['country' => $pais, 'type' => $tipoEstandarizado]
            );

            $nombreLargo = strtoupper($fila[3]);
            
            if (str_contains($nombreLargo, 'MEMORANDO')) {
                $tipoId = $tipos['memorando']->id;
            } elseif (str_contains($nombreLargo, 'ADENDA')) {
                $tipoId = $tipos['adenda']->id;
            } elseif (str_contains($nombreLargo, 'ESPECIFICO') || str_contains($nombreLargo, 'ESPECÍFICO')) {
                $tipoId = $tipos['especifico']->id;
            } else {
                $tipoId = $tipos['marco']->id;
            }

            $agreement = Agreement::firstOrCreate(
                ['resolution_number' => $fila[0]],
                [
                'title' => $fila[0],
                'name' => $fila[3],
                'resolution_number' => $fila[0],
                'institution_id' => $institucion->id,
                'agreement_type_id' => $tipoId, 
                'start_date' => $fila[4],
                'end_date' => $fila[5],
                'status' => 'Vigente'
            ]);

            $codigo = $fila[0];
            $anio = substr($codigo, -4); 
            $rutaRelativa = "convenios/{$anio}/{$codigo}.pdf"; 

            if (Storage::disk('public')->exists($rutaRelativa)) {
                $agreement->documents()->firstOrCreate(
                    ['file_path' => $rutaRelativa],
                    [
                    'name' => 'Doc - ' . ($agreement->resolution_number ?? $agreement->title),
                    'file_path' => $rutaRelativa,
                    'extension' => 'pdf'
                ]);
            }
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
