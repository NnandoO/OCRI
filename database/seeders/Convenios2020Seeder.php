<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Agreement;
use App\Models\Institution;
use App\Models\AgreementType;
use Illuminate\Support\Facades\DB;

class Convenios2020Seeder extends Seeder
{
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // 1. Crear los 3 tipos de alianza oficiales
        $tipos = [
            'marco' => AgreementType::firstOrCreate(['name' => 'Convenio Marco']),
            'especifico' => AgreementType::firstOrCreate(['name' => 'Convenio Específico']),
            'memorando' => AgreementType::firstOrCreate(['name' => 'Memorando de Entendimiento']),
        ];
        
        // Estructura: [0: Código, 1: Institución, 2: Tipo de Institución, 3: Nombre del convenio, 4: Fecha Inicio, 5: Fecha Fin, 6: País]
        $datos = [
            ['001-2020', 'MUNICIPALIDAD DISTRITAL DE CHONGOS ALTO', 'Municipalidad', 'CONVENIO DE COOPERACION INTERINSTITUCIONAL ENTRE LA MUNICIPALIDAD DISTRITAL DE CHONGOS ALTO Y LA UNCP', '2020-01-14', '2023-01-14', 'Perú'],
            ['002-2020', 'MUNICIPALIDAD DISTRITAL DE VIQUES', 'Municipalidad', 'CONVENIO ESPECÍFICO DE COOPERACIÓN INTERINSTITUCIONAL ENTRE LA UNIVERSIDAD NACIONAL DEL CENTRO DEL PERÚ Y LA MUNICIPALIDAD DISTRITAL DE VIQUES', '2019-11-29', '2021-11-29', 'Perú'],
            ['003-2020', 'INSTITUTO NACIONAL DE INNOVACION AGRARIA (INIA)', 'Otros', 'CONVENIO MARCO DE COOPERACION INTERINSTITUCIONAL ENTRE EL INSITITUTO NACIONAL DE INNOVACION AGRARIA (INIA)Y LA UNCP', '2019-05-24', '2024-05-24', 'Perú'],
            ['004-2020', 'COLEGIO DE PROFESIONALES DE ANTROPOLOGOS DEL PERU - CONSEJO DIRECTIVO DESCENTRALIZADO REGION CENTRO', 'Otros', 'CONVENIO ESPECIFICO DE COOPERACION INTERINSTITUCIONAL ENTRE LA UNCP Y EL COLEGIO DE PROFESIONALES DE ANTROPOLOGOS DEL PERU - CONSEJO DIRECTIVO DESCENTRALIZADO REGION CENTRO', '2020-01-31', '2023-01-31', 'Perú'],
            ['005-2020', 'UNIVERSIDAD DE MATANZAS DE CUBA', 'Universidad Internacional', 'CONVENIO MARCO DE COLABORACION ENTRE LA UNCP Y LA UNIVERSIDAD DE MATANZAS DE CUBA', '2020-01-31', '2025-01-31', 'Cuba'],
            ['006-2020', 'UNIVERSIDAD DE MATANZAS DE CUBA', 'Universidad Internacional', 'CONVENIO ESPECIFICO DE COLABORACION ENTRE LA UNCP Y LA UNIVERSIDAD DE MATANZAS DE CUBA', '2020-01-31', '2025-01-31', 'Cuba'],
            ['007-2020', 'INSTITUTO NACIONAL DE INNOVACION AGRARIA', 'Otros', 'CONVENIO MARCO DE COOPERACION INTERINSTITUCIONAL ENTRE EL INSTITUTO NACIONAL DE INNOVACION AGRARIA Y LA UNIVERSIDAD NACIONAL DEL CENTRO DEL PERU', '2020-01-31', '2025-01-31', 'Perú'],
            ['008-2020', 'MUNICIPALIDAD DISTRITAL DE RIO NEGRO', 'Municipalidad', 'CONVENIO DE COOPERACION INTERINSTITUCIONAL ENTRE LA MUNICIPALIDAD DISTRITAL DE RIO NEGRO Y LA FACULTAD DE CIENCIAS AGRARIAS DE LA UNIVERSIDAD NACIONAL DEL CENTRO DEL PERU PARA EL FUNCIONAMIENTO DE UN LABORATORIO DE ANALISIS DE SUELOS, AGUAS, PLANTAS Y FERTILIZANTES', '2020-01-15', '2021-01-15', 'Perú'],
            ['009-2020', 'INSTITUTO DE EDUCACION TECNOLOGICO PUBLICO SANTIAGO ANTUNEZ DE MAYOLO', 'Institución Educativa', 'CONVENIO MARCO DE COOPERACION INTERINSTITUCIONAL ENTRE LA UNIVERSIDAD NACIONAL DEL CENTRO DEL PERU Y EL ISNTITUTO DE EDUCACION TECNOLOGICO PUBLICO SANTIAGO ANTUNEZ DE MAYOLO', '2020-01-28', '2025-01-28', 'Perú'],
            ['010-2020', 'MINISTERIO DE RELACIONES EXTERIORES', 'Otros', 'CONVENIO MARCO DE COOPERACION INTERINSTITUCIONAL EN ASUNTOS ATARTICOS ENTRE EL MINISTERIO DE RELACIONES EXTERIORES Y LA UNIVERSIADAD NACIONAL DEL CENTRO DEL PERU - UNCP', '2019-10-23', '2024-10-23', 'Perú'],
            ['011-2020', 'EMPRESA DE CONCEPCION LACTEOS', 'Empresa Nacional', 'CONVENIO MARCO DE COOPERACION INTERINSTITUCIONAL ENTRE LA UNIVERSIDAD NACIONAL DEL CENTRO DEL PERU Y LA EMPRESA DE CONCEPCION LACTEOS', '2019-10-10', '2024-10-10', 'Perú'],
            ['012-2020', 'MUNICIPALIDAD DISTRITAL DE MUQUIYAUYO JAUJA - JUNIN', 'Municipalidad', 'MEMORANDO DE ENTENDIMIENTO ENTRE LA MUNICIPALIDAD DISTRITAL DE MUQUIYAUYO JAUJA - JUNIN Y LA UNIVERSIDAD NACIONAL DEL CENTRO DEL PERÚ', '2020-03-02', '2025-03-02', 'Perú'],
            ['013-2020', 'ORGANISMO DE EVALUACIÓN Y FISCALIZACIÓN AMBIENTAL - OEFA', 'Otros', 'CONVENIO ESPECÍFICO DE COOPERACIÓN INTERINSTITUCIONAL ENTRE EL ORGANISMO DE EVALUACIÓN Y FISCALIZACIÓN AMBIENTAL - OEFA Y LA UNIVERSIDAD NACIONAL DEL CENTRO DEL PERÚ', '2020-03-02', '2023-03-02', 'Perú'],
            ['014-2020', 'INSTITUTO GEOFÍSICO DEL PERÚ', 'Otros', 'CONVENIO MARCO DE COOPERACIÓN INTERINSTITUCIONAL ENTRE EL INSTITUTO GEOFÍSICO DEL PERÚ Y LA UNCP', '2020-02-18', '2025-02-18', 'Perú'],
            ['015-2020', 'DIRECCIÓN EJECUTIVA DE LA RED DE SALUD VALLE DEL MANTARO', 'Salud', 'CONVENIO ESPECÍFICO DE COOPERACIÓN DOCENTE ASISTENCIAL ENTRE LA DIRECCIÓN EJECUTIVA DE LA RED DE SALUD VALLE DEL MANTARO Y LA UNCP', '2020-02-25', '2023-02-25', 'Perú'],
            ['016-2020', 'MINISTERIO DE EDUCACIÓN', 'Otros', 'CONVENIO DE COLABORACIÓN INTERINSTITUCIONAL ENTRE EL MINISTERIO DE EDUCACIÓN Y LA UNCP', '2020-03-05', '2020-12-31', 'Perú'],
            ['017-2020', 'COOPERATIVA AGRARIA AGROPIA LTDA.', 'Empresa Nacional', 'CONVENIO MARCO DE COOPERACIÓN INTERINSTITUCIONAL ENTRE LA COOPERATIVA AGRARIA AGROPIA LTDA. Y LA UNCP', '2020-06-18', '2025-06-18', 'Perú'],
            ['018-2020', 'ONG ATIYCUY PERÚ', 'Otros', 'CONVENIO MARCO DE COOPERACION INTERINSTITUCIONAL ENTRE LA UNIVERSIDAD NACIONAL DEL CENTRO DEL PERÚ Y LA ONG ATIYCUY PERÚ', '2020-09-01', '2022-09-01', 'Perú'],
            ['019-2020', 'INSTITUTO GEOFÍSICO DEL PERÚ', 'Otros', 'CONVENIO ESPECÍFICO DE COOPERACIÓN INTERINSTITUCIONAL ENTRE EL INSTITUTO GEOFÍSICO DEL PERÚ Y LA UNCP', '2020-10-07', '2022-10-07', 'Perú'],
            ['020-2020', 'MUNICIPALIDAD PROVINCIAL YAULI - LA OROYA', 'Municipalidad', 'CONVENIO DE COLABORACIÓN INTERINSTITUCIONAL ENTRE LA UNIVERSIDAD NACIONAL DEL CENTRO DEL PERÚ Y LA MUNICIPALIDAD PROVINCIAL YAULI - LA OROYA', '2020-07-15', '2021-07-15', 'Perú'],
            ['021-2020', 'ONG PROBIODIVERSIDAD AC, MÉXICO', 'Otros', 'CONVENIO MARCO DE COOPERACIÓN INTERINSTITUCIONAL ENTRE LA UNIVERSIDAD NACIONAL DEL CENTRO DEL PERÚ Y LA ONG PROBIODIVERSIDAD AC, MÉXICO.', '2020-10-19', '2025-10-19', 'México'],
            ['022-2020', 'EMPRESA EREBOR COMPAÑÍA MINERA S.R.L.', 'Empresa Nacional', 'ADENDA AL CONVENIO ESPECÍFICO DE CONCESIÓN PLANTA CONCENTRADORA PILOTO DE MINERALES HUARI DE LA UNIVERSIDAD NACIONAL DEL CENTRO DEL PERÚ Y LA EMPRESA EREBOR COMPAÑÍA MINERA S.R.L.', '2020-05-08', '2024-05-08', 'Perú'],
            ['023-2020', 'INNOVATE PERÚ', 'Otros', 'CONVENIO DE ASOCIACION PARA LA EJECUCIÓN DE PROYECTO', '2020-10-20', null, 'Perú'],
            ['024-2020', 'EMPRESA MEEC ENGINEERING SAC', 'Empresa Nacional', 'CONVENIO MARCO DE COOPERACIÓN INTERINSTITUCIONAL ENTRE LA UNCP Y LA EMPRESA MEEC ENGINEERING SAC', '2020-10-17', '2025-10-17', 'Perú'],
            ['025-2020', 'COLEGIO DE POSTGRADUADOS EN CIENCIAS AGRÍCOLAS DE MEXICO', 'Universidad Internacional', 'CONVENIO MARACO DE COOPERACIÓN INTERINSTITUCIONAL ENTRE LA UNCP Y EL COLEGIO DE POSTGRADUADOS EN CIENCIAS AGRÍCOLAS DE MEXICO', '2020-11-13', '2025-11-13', 'México'],
            ['026-2020', 'MUNICIPALIDAD DISTRITAL DE ÑAHUIMPUQUIO', 'Municipalidad', 'CONVENIO MARCO DE COOPERACIÓN INTERINSTITUCIONAL ENTRE LA UNCP Y LA MUNICIPALIDAD DISTRITAL DE ÑAHUIMPUQUIO', '2020-10-15', '2025-10-15', 'Perú'],
            ['027-2020', 'UNIVERSIDAD DISTRITAL FRANCISCO JOSÉ DE CALDAS - COLOMBIA', 'Universidad Internacional', 'CONVENIO DE MOVILIDAD ACADÉMICA DE ESTUDIOS Y DOCENTES SUSCRITO ENTRE LA UNIVERSIDAD DISTRITAL FRANCISCO JOSÉ DE CALDAS - COLOMBIA Y LA UNCP', '2020-11-19', '2025-11-19', 'Colombia'],
            ['028-2020', 'UNIVERSIDAD DISTRITAL FRANCISCO JOSÉ DE CALDAS – UDFJC (COLOMBIA)', 'Universidad Internacional', 'CONVENIO MARCO DE COOPERACIÓN ACADÉMICA CELEBRADO ENTRE LA UNIVERSIDAD DISTRITAL FRANCISCO JOSÉ DE CALDAS – UDFJC (COLOMBIA) y LA UNIVERSIDAD NACIONAL DEL CENTRO DEL PERÚ –UNCP (PERÚ)', '2020-11-19', '2025-11-19', 'Colombia'],
            ['029-2020', 'SAPIENZA UNIVERSIDAD DE ROMA (ITALIA)', 'Universidad Internacional', 'CONVENIO MARCO DE COOPERACIÓN CULTURAL Y CIENTÍFICA ENTRE SAPIENZA UNIVERSIDAD DE ROMA (ITALIA) Y LA UNIVERSIDAD NACIONAL DEL CENTRO DEL PERÚ', '2019-08-28', '2024-08-28', 'Italia'],
            ['030-2020', 'EMPRESA JOYERÍA MIZUMI S.A.C.', 'Empresa Nacional', 'CONVENIO MARCO DE COOPERACIÓN INTERINSTITUCIONAL ENTRE LA UNIVERSIDAD NACIONAL DEL CENTRO DEL PERÚ Y LA EMPRESA JOYERÍA MIZUMI S.A.C.', '2020-10-05', '2025-10-05', 'Perú'],
            ['031-2020', 'INDECOPI', 'Otros', 'ADENDA AL CONVENIO DE COLABORACIÓN PARA EL ESTABLECIMIENTO DE UN CENRO DE APOYO A LA TECNOLOGÍA Y LA INNOVACIÓN (CATI) EN LA UNIVERSIDAD NACIONAL DEL CENTRO DEL PERÚ COMO INSTITUCIÓN INTEGRANTE DE LA RED NACIONAL DE CATI EN PERÚ', '2020-04-07', '2022-04-06', 'Perú'],
            ['032-2020', 'SAPIENZA UNIVERSIDAD DE ROMA (ITALIA)', 'Universidad Internacional', 'PROTOCOLO EJECUTIVO DEL CONVENIO MARCO ENTRE SAPIENZA UNIVERSIDAD DE ROMA (ITALIA) Y LA UNIVERSIDAD NACIONAL DEL CENTRO DEL PERÚ', '2020-03-03', '2021-03-03', 'Italia'],
            ['033-2020', 'UNIVERSIDAD FEDERAL DE LAVRAS', 'Universidad Internacional', 'PRIMER TÉRMINO ADICIONAL DEL ACUERDO DE COOPERACIÓN MUTUA N° 018-2015  UFLA, CELEBRADO ENTRE LA UNIVERSIDAD FEDERAL DE LAVRAS Y LA UNIVERSIDAD NACIONAL DEL CENTRO DEL PERÚ', '2020-10-05', '2025-10-05', 'Brasil'],
        ];

        foreach ($datos as $fila) {
            
            $nombreInstitucion = strtoupper($fila[1]);
            $tipoInstitucion = $fila[2];
            $pais = $fila[6];

            // Agrupar comunidades
            if (str_contains($nombreInstitucion, 'COMUNIDAD')) {
                $nombreInstitucion = 'COMUNIDADES CAMPESINAS Y NATIVAS';
                $tipoInstitucion = 'Comunidad';
            }

            // Buscar o crear la institución
            $institucion = Institution::firstOrCreate(
                ['name' => $nombreInstitucion],
                ['country' => $pais, 'type' => $tipoInstitucion]
            );

            // Asignar el ID del tipo de convenio
            $nombreLargo = strtoupper($fila[3]);
            
            if (str_contains($nombreLargo, 'MEMORANDO')) {
                $tipoId = $tipos['memorando']->id;
            } elseif (str_contains($nombreLargo, 'ESPECIFICO') || str_contains($nombreLargo, 'ESPECÍFICO') || str_contains($nombreLargo, 'ADENDA') || str_contains($nombreLargo, 'PROTOCOLO')) {
                $tipoId = $tipos['especifico']->id; 
            } else {
                $tipoId = $tipos['marco']->id;
            }

            // Crear el convenio
            // 1. Creamos el convenio y lo guardamos en una variable
            $agreement = Agreement::create([
                'title' => $fila[0],
                'name' => $fila[3],
                'resolution_number' => $fila[0],
                'institution_id' => $institucion->id,
                'agreement_type_id' => $tipoId, 
                'start_date' => $fila[4],
                'end_date' => $fila[5],
                'status' => 'Vigente'
            ]);

            // 2. Lógica actualizada para buscar en subcarpetas por año
            $codigo = $fila[0]; // Ej: '001-2024'
            
            // Extraemos los últimos 4 caracteres del código para obtener el año (ej: '2024')
            $anio = substr($codigo, -4); 
            
            // Armamos la ruta incluyendo la carpeta del año
            $rutaRelativa = "convenios/{$anio}/{$codigo}.pdf"; 

            // Verificamos si existe y lo guardamos
            if (\Illuminate\Support\Facades\Storage::disk('public')->exists($rutaRelativa)) {
                $agreement->documents()->create([
                    'name' => 'Doc - ' . ($agreement->resolution_number ?? $agreement->title),
                    'file_path' => $rutaRelativa,
                    'extension' => 'pdf'
                ]);
            }
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}