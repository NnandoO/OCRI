<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Agreement;
use App\Models\Institution;
use App\Models\AgreementType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class Convenios2015Seeder extends Seeder
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
            ['002-2015', 'HUSRI', 'Empresa Internacional', 'CONVENIO MARCO DE COOPERACION INSTERINSTITUCIONAL ENTRE LA MUNICIPALIDAD PROVINCIAL DE CHUPACA Y LA UNCP', null, null, 'Internacional'],
            ['003-2015', 'AUTORIDAD AUTÓNOMA DEL SISITEMA ELÉCTRICO DE TRANSPORTE MASIVO DE LIMA Y CALLAO-AATE', 'Empresa Internacional', 'CONVENIO MARCO DE COLABORACION INSTERISNTITUCIONAL ENTRE LA AUTORIDAD AUTÓNOMA DEL SISTEMA ELÉCTRICO DE TRANSPORTE MASIVO DE LIMA Y CALLAO - AATE Y LA UNCP', '2015-02-25', '2020-02-25', 'Internacional'],
            ['004-2015', 'UNIDAD DE GESTIÓN EDUCATIVA LOCAL DE SAN MARTIN DE PANGOA, MUNICIPALIDAD DEL CENTRO POBLADO VILLA MARÍA, MUNICIAPLIDAD', 'Sector Público', 'CONVENIO MARCO DE COOPERACION INSTERINSTITUCIONAL ENTRE LA UNIDAD DE GESTION EDUCATIVA LOCAL DE SAN MARTIN DE PANGOA, LA MUNICPALIDAD DEL CENTRO POBLADO VILLA MARIA, MUNICIPALIDAD DISTRITAL DE SAN ...', '2015-02-15', null, 'Internacional'],
            ['005-2015', 'MUNICIPALIDAD DISTRITAL DE SAN JUAN - PROVINCIA DE SIHUAS - ANCASH', 'Sector Público', 'CONVENIO DE COOPERACION INTERINSTITUCIONAL ENTRE LA MUNICIPALIDAD DISTRITAL DE SAN JUAN - PROVINCIA SIHUAS- ANCASH Y LA UNCP', '2015-04-17', '2018-04-17', 'Internacional'],
            ['006-2015', 'UNIVERSIDAD ARTURO PRAT DE CHILE', 'Universidad Internacional', 'CONVENIO MARCO DE COOPERACION ACADÉMICA E INTERCAMBIO ENTRE LA UNCP Y LA UNIVERSIDAD ARTURO PRAT', null, null, 'Internacional'],
            ['007-2015', 'UNIVERSIDAD DE ANTOFAGASTA - UNCP', 'Universidad Internacional', 'CONVENIO DE COOPERACION ACADÉMICA E INTERCAMBIO ENTRE LA UNIVERSIDAD NACIONAL DEL CENTRO DEL PERÚ Y LA UNIVERSIDAD DE ANTOFAGASTA', '2015-05-20', '2020-05-20', 'Internacional'],
            ['008-2015', 'HOSPITAL FELIX MAYORCA SOTO DE TARMA', 'Salud', 'CONVENIO ESPECÍFICO DE COOPERACION DOCENTE ASISTENCIAL ENTRE EL HOSPITAL FELIX MAYORCA SOTO DE TARMA Y LA FACULTAD DE MEDICINA HUMANA DE LA UNCP', null, '2019-04-30', 'Internacional'],
            ['012-2015', 'UNIVERSIDAD NACIONAL AGRARIA LA MOLINA', 'Universidad Nacional', 'CONVENIO MARCO DE COOPERACION ACADÉMICA CIENTIFICA Y CULTRAL ENTRE LA UNIVERSIDAD NACIONAL AGRARIA LA MOLINA Y LA UNCP', '2015-05-28', '2018-05-28', 'Internacional'],
            ['015-2015', 'FACULTAD INGENIERPIA DEMINAS - CIVIL - AMBIENTAL DE LA UNIVERSIDAD NACIONAL DE HUANCAVELICA', 'Universidad Nacional', 'CONVENIO ESPECIFICO DE COLABORACION ENTRE LA FACULTAD DE INGENIERIA DE MINAS - CIVIL -AMBIENTAL DE LA UNIVERSIDAD NACIONAL DE HUANCAVELICA Y LA FACULTAD DE INGENIERÍA QUÍMICA DE LA UNCP', '2015-05-12', '2020-05-12', 'Internacional'],
            ['017-2015', 'CAJA RURAL DE AHRRRO Y CRÉDITO DEL CENTRO', 'Empresa Internacional', 'CONVENIO DE COOPERACION INTERINSTITUCIONAL PARA EL OTORGAMIENTO DE PRESTAMOS PERSONALES CON DESCUENTO POR PLANILLA DE REMUNERACIONES CAJA RURAL DE AHORRO Y CREDITO DEL CENTRO', null, null, 'Internacional'],
            ['019-2015', 'UNIVERSIDAD DSTRITAL FRANCISCO CLADAS - UDF JC - COLOMBIA', 'Universidad Internacional', 'CONVENIO MARCO DE COOPERACION ACADÉMICA CELEBRADO ENTRE LA UNIVERSIDAD DISTRITAL FRANCISCO JOSÉ CALDAS -UDFJC (COLOMBIA) Y LA UNCP', '2015-07-21', '2020-07-21', 'Internacional'],
            ['020-2015', 'UNIVERSIDAD DSTRITAL FRANCISCO CLADAS - UDF JC - COLOMBIA', 'Universidad Internacional', 'CONVENIO DE MOVILIDAD ACADÉMICA DE ESTUDIANTES Y DOCENTES SUSCRITO ENTRE LA UNIVERSIDAD DISTRITAL FRANCISCO JOSÉ DE CALDAS - UDFJC (COLOMBIA)  Y LA UNCP', '2015-07-21', '2020-07-21', 'Internacional'],
            ['021-2015', 'COLEGIO DE PSICÓLOGOS DEL PERÚ', 'Educación', 'CONVENIO MARCO DE COOPERACION INTERINSTITUCIONAL ENTRE EL COLEGIO DE PSICOLOGOS DEL PERU CONSEJO DIRECTIVO REGIONAL II Y LA UNCP', null, null, 'Internacional'],
            ['022-2015', 'UNIVERSIDAD ESTATAL DEWAYNE - DETROIT, MICHIGAN, ESTADOS UNIDOS', 'Universidad Internacional', 'ACUERDO DE COOPERACION Y MEMORANDO DE ENTENDIMIENTO SUSCRITO ENTRE LA UNIVERSIDAD ESTATAL DE WAYNE Y LA UNCP', '2015-08-20', '2020-08-20', 'Internacional'],
            ['023-2015', 'UNIVERSIDAD TECNOLÓGICA METROPOLITANA - CHILE', 'Universidad Internacional', 'CONVENIO DE COOPERACION ACADEMICA E INTERCAMBIO ENTRE LA UNCP Y LA UNIVERSIDAD TECNOLOGICA METROPOLITANA DE CHILE', '2015-06-15', '2020-06-15', 'Internacional'],
            ['024-2015', 'UNIVERSIDAD DE LOS LAGOS - CHILE', 'Universidad Internacional', 'CONVENIO DE COOPERACION ACADÉMICA E INTERCAMBIO ENTRE LA UNCP Y LA UNIVERSIDAD DE LOS LAGOS - CHILE', '2015-09-23', '2020-09-23', 'Internacional'],
            ['027-2015', 'UNIVERSIDA CATÓLICA DEL ORIENTE - COLOMBIA', 'Empresa Internacional', 'CONVENIO DE COOPERACION ACADÉMICA E INTERCAMBIO ENTRE LA UNCP Y LA UNIVERSIDAD CATÓLICA DEL ORIENTE - UCO DE COLOMBIA', '2015-09-28', '2015-09-28', 'Internacional'],
            ['028-2015', 'UNIVERSIDAD ANTONIO NARIÑO - COLOMBIA', 'Universidad Internacional', 'CONVENIO DE COOPERACION ACADÉMICA E INTERCAMBIO ENTRE LA UNCP Y LA UNVIERSIDAD ANTONIO NARIÑO DE COLOMBIA', '2015-09-28', '2020-09-28', 'Internacional'],
            ['029/2015', 'UNIVERSIDAD SIMÓN BOLIVAR DE COLOMBIA', 'Universidad Internacional', 'CONVENIO DE COOPERACION ACADEMICA E INTERCAMBIO ENTRE LA UNCP Y LA UNIVERSIDAD SIMON BOLIVAR DE COLOMBIA', '2015-09-28', '2020-09-28', 'Internacional'],
            ['030-2015', 'UNIVERSIDAD AUTÓNOMA METROPOLITANA, MÉXICO', 'Universidad Internacional', 'CONVENIO GENERAL DE COOPERACION ENTRE LA UNIVERSIDAD AUTONOMA METROPOLITANA DE MEXICO Y LA UNCP', '2015-04-21', '2019-04-21', 'Internacional'],
            ['031-2015', 'UNIVERSIDAD AUTÓNOMA METROPOLITANA, MÉXICO', 'Universidad Internacional', 'CONVENIO ESPECIFICO EN MATERIA DE INTERCAMBIO DE ALUMNOS Y PERSONAL ACADÉMICO ENTRE LA UNIVERSIDAD AUTONOMA METROPOLITANA Y LA UNCP', '2015-04-29', '2018-04-29', 'Internacional'],
            ['032-2015', '', 'Otros', 'ACUERDO DE COLABORACIÓN ENTRE LA UNIVERSIDAD AUTÓNOMA DE QUERÉTARO, DE LOS ESTADOS UNIDOS MEXICANOS Y LA UNIVERSIDAD NACIONAL DEL CENTRO DEL PERÚ', null, null, 'Perú'],
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
