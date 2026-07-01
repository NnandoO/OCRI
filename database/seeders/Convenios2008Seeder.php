<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Agreement;
use App\Models\Institution;
use App\Models\AgreementType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class Convenios2008Seeder extends Seeder
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
            ['007-2008', 'SEGURO SOCIAL DE SALUD - ESSALUD', 'Salud', 'CONVENIO CON EL SEGURO SOCIAL DE SALUD – ESSALUD CONSESION DE EL FUNDO EL PORVENIR', '2008-02-22', '2068-02-22', 'Internacional'],
            ['033 - 2008', 'MUNICIPALIDA PROVINCIAL DE SATIPO', 'Empresa Internacional', 'CONVENIO ESPECÍFICO ENTRE LA MUNICIPALIDAD PROVINCIAL DE SATIPO Y LA UNIVERSIDAD NACIONAL DEL CENTRO DEL PERÚ – ESTACIÓN EXPERIMENTAL AGROPECUARIA SATIPO PROYECTO “MEJORAMIENTO DEL CULTIVO DE ARROZ...', '2008-09-05', '2018-09-05', 'Internacional'],
            ['049-2008', 'BANCO DE LA NACIÓN', 'Empresa Nacional', 'CONVENIO DE COLABORACIÒN INTERINSTITUCIONAL ENTRE LA UNIVERSIDAD NACIONAL DEL CENTRO DEL PERÙ Y EL BANCO DE LA NACIÓN', '2008-06-09', null, 'Internacional'],
            ['050-2008', 'BANCO DE LA NACIÓN', 'Empresa Nacional', 'CONVENIO DE COLABORACIÒN INTERINSTITUCIONAL ENTRE LA UNIVERSIDAD NACIONAL DEL CENTRO DEL PERÙ Y EL BANCO DE LA NACIÓN', '2008-05-29', null, 'Internacional'],
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
