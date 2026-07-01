<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Agreement;
use App\Models\Institution;
use App\Models\AgreementType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class Convenios2013Seeder extends Seeder
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
            ['008-2013', 'UNIVERSIDAD NACIONAL ALCIDES CARRIÓN', 'Universidad Nacional', 'CONVENIO ESPECÍFICO DE COOPERACIÓN ENTRE LA UNIVERSIDAD NACIONAL DANIEL ALCIDES CARRIÓN Y LA UNIVERSIDAD NACIONAL DEL CENTRO DEL PERÚ', '2013-04-15', '2018-04-15', 'Internacional'],
            ['018-2013', 'UNIVERSIDAD MAYOR DE SAN MARCOS', 'Universidad Internacional', 'CONVENIO MARCO DE COOPERACIÓN ACADÉMICA CIENTÍFICA Y CULTURAL ENTRE LA UNIVERSIDAD MAYOR DE SAN MARCOS Y LA UNCP', '2013-08-13', '2018-08-13', 'Internacional'],
            ['026-2013', 'MUNICIPALIDAD DISTRITAL DE RÍO NEGRO', 'Sector Público', 'CONVENIO ESPECÍFICO DE COOPERACIÓN INTERINSTITUCIONAL ENTRE LA UNCP Y LA MUNICIPALIDAD DISTRITAL DE RÍO NEGRO', null, '2028-09-04', 'Internacional'],
            ['027-2013', 'UNIVERSIDAD NACIONAL TORIBIO RODRIGUEZ DE MENDOZA DE AMAZONAS', 'Universidad Nacional', 'CONVENIO MARCO DE COOPERACIÓN ACADÉMICA CIENTÍFICA Y CULTURAL ENTRE LA UNIVERISIDAD NACIONAL TORIBIO RODRIGUEZ DE MENDOZA DE AMANZONAS Y LA UNCP', '2013-09-09', '2018-09-09', 'Internacional'],
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
