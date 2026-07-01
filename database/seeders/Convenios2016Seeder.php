<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Agreement;
use App\Models\Institution;
use App\Models\AgreementType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class Convenios2016Seeder extends Seeder
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
            ['003-2016', 'UNIVERSIDAD DEL PACÍFICO', 'Educación', 'CONVENIO ESPECIFICO DE COOPERACION ACADÉMICA ENTRE LA UNIVERSIDAD DEL PACÍFICO Y LA UNCP', null, '2020-11-26', 'Perú'],
            ['006-2016', 'PRONABEC', 'Otros', 'CONVENIO Nº 012-2016-MINEDU/VMGI- PRONABEC CONVENIO DE COOPERACIÓN INTERNIENTITUCIONAL PARA LA IMPLEMENTACIÓN DE BECAS ENTRE EL PROGRAMA NACIONAL DE BECAS Y CREDITO EDUCATIVO Y LA UNCP', '2016-02-01', '2021-02-01', 'Perú'],
            ['007-2016', 'SERVIR', 'Otros', 'CONVENIO MARCO PARA LA ASIGNACION DE GERENTES PÚBLICOS QUE CELEBRAN LA AUTORIDAD NACIONAL DEL SERVICIO CIVIL Y LA UNCP', '2015-12-15', null, 'Perú'],
            ['008-2016', 'IESTPLAM', 'Educación', 'CONVENIO DE COOPERACION INTERINSTITUCIONAL ENTRE LA UNCP Y EL INSTITUTO DE EDUCACIÓN SUPERIOR TECNOLÓGICO PÚBLICO LA MERCED', '2015-12-31', '2018-11-30', 'Perú'],
            ['010-2016', 'UNI', 'Otros', 'CONVENIO MARCO DE COLABORACIÓN INTERINSTITUCIONAL ENTRE LA UNCP Y LA UNIVERSIDAD NACIONAL DE  INGENIERÍA', '2016-03-01', '2019-03-01', 'Perú'],
            ['011-2016', 'UNIVERSIDAD E HUANCAVELICA', 'Educación', 'CONVENIO MARCO DE COOPERACION ACADÉMICA CIENTÍFICA Y CULTURAL ENTRE LA UNCP Y LA UNIVERSIDAD DE HUANCAVELICA', '2016-03-09', '2021-03-09', 'Perú'],
            ['014-2016', 'RED ASISTENCIAL DE JUNÍN', 'Salud', 'CONVENIO ESPECIFIFO ENTRE ESSALUD-RED ASISTENCIAL JUNÍN Y LA UNCP-FACULTAD  DE MEDICINA HUMANA', null, null, 'Perú'],
            ['015-2016', 'PROVAL', 'Otros', 'CONVENIO DE COOPERACION INTERINSTITUCIONAL ENTRE EL MINISTERIO INTERNACIONAL E.M.A.U.S Y LA UNCP', '2016-04-29', '2018-04-29', 'Internacional'],
            ['016-2016', 'MUNICIPALIDAD DISTRITAL DE CHUPURO', 'Sector Público', 'CONVENIO DE COOPERACION INTERINSTITUCIONAL ENTRE LA UNCP Y LA MUNICIPALIDAD DISTRITAL DE CHUPURO', '2016-03-07', null, 'Perú'],
            ['021-2016', 'FUDEC PERÚ', 'Otros', 'CONVENIO MARCO DE COOPERACION ENTRE LA UNCP Y LA FUDEC', '2016-04-20', null, 'Perú'],
            ['024-2016', 'COMUNIDAD CAMPESINA DE PUMACOCHA', 'Comunidades', 'CONVENIO MARCO DE COOPERACION INTERINSTITUCIONAL ENTRE LA UNCP Y LA COMUNIDAD CAMPESINA DE PUCACOCHA ANDAMARCA PROVINCIA DE CONCEPCION', '2016-06-14', '2018-06-14', 'Perú'],
            ['025-2016', 'UNIVERSIDAD E HUANCAVELICA', 'Educación', 'CARTA DE INTENCION ENTRE LA UNIVERSIDAD NACIONAL DE HUANCAVELICA Y LA UCNP', '2016-06-14', '2018-06-14', 'Perú'],
            ['026-2016', 'MINISTERIO DE SALUD, GOBIERNOO REGIONAL DEL CALLAO', 'Sector Público', 'CONVENIO MARCO DE COOPERACION DOCENTE ASISTENCIAL ENTRE EL MINISTERIO DE SALUD, EL GOBIERNO REGIONAL DEL CALLAO Y LA UNCP', '2016-06-09', '2020-06-09', 'Perú'],
            ['027-2016', 'PROGRAMA NACIONAL DE BECAS Y CRÉDITO EDUCATIVO - PRONABEC', 'Sector Público', 'CONVENIO ESPECÍFICO DE COOPERACION INTERINSTITUCIONAL PARA LA IMPLEMENTACIÓN DE LA BECA DE PERMANENCIA DE ESTUDIOS - NACIONAL ENTRE PRONABEC Y LA UNCP', '2016-06-07', '2021-06-07', 'Perú'],
            ['028-2016', 'CÁMARA MINERA DE L PERÚ', 'Otros', 'CONVENIO MARCO DE COOPERACION INTERINSTITUCIONAL ENTRE LA UNCP Y LA CAMARA MINERA DEL PERU', '2016-06-25', null, 'Perú'],
            ['029-2016', 'HOSPITAL DOMINGO OLAVEGOYA DE JAUJA', 'Salud', 'CONVENIO ESPECÍFICO DE COOPERACION DOCENTE ASISTENCIAL ENTRE EL HOSPITAL DOMINGO OLAVEGOYA DE JAUJA Y LA UNCP - FACULTAD DE MEDICINA HUMANA', '2016-06-15', '2020-06-14', 'Perú'],
            ['031-2016', 'MUNICIPALIDAD DISTRTIAL DE RÍO NEGRO', 'Sector Público', 'CONVENIO INTERINSTITUCIONAL ENTRE LA MUNICIPALIDAD DISTRITAL DE RIO NEGRO Y LA UNCP - CIENCIAS AGRARIAS SATIPO', '2016-08-01', '2019-08-01', 'Perú'],
            ['033-2016', 'MINISTERIO DE VIVIENDA, CONSTRUCCIÓN Y SANEAMIENTO', 'Sector Público', 'CONVENIO MARCO DE COOPERACION INTERINSTITUCIONAL ENTRE EL MINISTERIO DE VIVIENDA, CONSTRUCCION Y SANEAMIENTO Y LA UNIVERSIDAD NACIONAL DEL CENTRO DEL PERU', '2016-08-01', '2019-08-01', 'Perú'],
            ['034-2016', 'COLEGIO DE PERIODISTAS DEL PERÚ', 'Educación', 'CONVENIO DE COOPERACION INTERINSTITUCIONAL ENTRE LA UNIVERSIDAD NACIONAL DEL CENTRO DEL PERU Y EL COLEGIO DE PERIODISTAS', '2016-06-25', '2020-06-25', 'Perú'],
            ['035-2016', 'CENTRO AGRONOMICO TROPICAL DE INVESTIGACIÓN Y ENSEÑANZA (CATIE)', 'Otros', 'MEMORANDUM DE ENTENDIMIENTO CATIE UNCP(MOU)', '2016-06-24', '2021-06-24', 'Perú'],
            ['039-2016', 'MUNICIPALIDAD DISTRITAL DE ACOLLA - JAUJA', 'Sector Público', 'CONVENIO EDUCATIVO DE COOPERACION Y COLABORACION INTERINSTITUCIONAL ENTRE EL CENTRO PRE-UNCP DEL PERU Y LA MUNICIPALIDAD DISTRITAL DE ACOLLA, PROVINCIA DE JAUJA- DEPARTAMENTO DE JUNIN', '2016-08-19', null, 'Perú'],
            ['040-2016', 'COMUNIDAD CAMPESINA DE HUARI', 'Comunidades', 'CONVENIO ENTRE LA COMUNIDAD CAMPESINA DE HUARI Y LA UNIVERSIDAD NACIONAL DEL CENTRO DEL PERU, CON FINES DE OBTENCION DEL PERMISO PARA LA UTILIZACION DE TERRENOS SUPERFICIALES DONDE SE UBICA LA PLAN...', null, null, 'Perú'],
            ['042-2016', '', 'Otros', 'CONVENIO ESPECÍFICO ENTRE LA UNIVERSIDAD NACIONAL DEL CENTRO DEL PERÚ Y LA FUNDACIÓN PARA EL DESARROLLO DEL CENTRO DEL PERÚ PARA COOPERACIÓN EN LA IMPLEMENTACIÓN DEL SERVICIO DE VIGILANCIA Y SEGURI...', '2016-07-01', null, 'Perú'],
            ['043-2016', 'COLEGIO DE INGENIEROS DEL PERÚ', 'Educación', 'CONVENIO MARCO DE COOPERACION INTERINSTITUCIONAL ENTRE EL COLEGIO DE INGENIEROS DEL PERÚ Y LA UNCP', null, '2019-03-07', 'Perú'],
            ['044-2016', 'FUNDACIÓN INNOVA CASTILLA DE LA MANCHA', 'Otros', 'CONVENIO CATEDRA DE COOPERACION INTERINSTITUCIONAL ENTRE LA   FUNDACION IN-NOVA CASTILLA LA MANCHA Y LA UNCP', '2016-10-11', '2018-10-11', 'Perú'],
            ['045-2016', 'ESCOLA SUPERIOR DE AGRICULTURA "LUIS DE QUEIROZ" DE LA   UNIVERSIDAD DE SAO PAULO', 'Educación', 'CONVENIO ACADEMICO INTERNACIONAL ENTRE LA ESCOLA SUPERIOR DE AGRICULTURA LUIZ DE QUEIROZ DE SAO PAULO  -BRASIL Y LA UNCP', '2016-10-20', '2021-10-20', 'Internacional'],
            ['047-2016', 'HOSPITAL DE HUAYCAN', 'Salud', 'CONVENIO ESPECÍFICO DE COOPERACION DOCENTE ASISTENCIAL ENTRE EL HOSPITAL DE HUAYCAN Y LA FACULTAD DE MEDICINA HUMANA', '2016-11-07', '2019-11-07', 'Perú'],
            ['048-2016', 'ASOCIACIÓN FERREYCORP', 'Otros', 'PRIMERA ADENDA AL CONVENIO DE COOPERACION ACADÉMICA ENTRE LA UNCP Y LA ASOCIACIÓN FERREYCORP', '2016-11-10', null, 'Perú'],
            ['049-2016', 'UNIVERSIDAD AUSTRTAL DE CHILE', 'Educación', 'MEMORANDUM DE ENTENDIMIENTO ENTRE LA UNIVERSIDAD AUSTRAL DE CHILE Y LA UNCP', null, null, 'Perú'],
            ['050-2016', 'UNIVERSIDAD DEL ESTADO DE MICHIGAN', 'Educación', 'CARTA DE INTENCIÓN PARA LA COOPERACION ENTRE LA UNIVERSIDAD DEL ESTADO DE MICHIGAN Y LA UNCP', null, null, 'Perú'],
            ['051-2016', 'CAJA DE AHORRO Y CRÉDITO HUANCAYO', 'Otros', 'CONVENIO ENTRE LAS INSTITUCIONES DE LA CAJA MUNICIPAL DE AHORRO Y CRÉDITO DE HUANCAYO S.A. Y LA UNCP PARA EL OTORGAMIENTO DE PRESTAMOS PRESONALES CON PROCEDIMIENTO DE DESCUENTO POR PLANILLA', '2016-12-07', '2019-12-07', 'Perú'],
            ['053-2016', 'HOSPITAL SUBREGIONAL DE ANDAHUAYLAS', 'Salud', 'CONVENIO ESPECÍFICO DE COOPERACION DOCENTE ASISTENCIAL ENTRE LA UNCP Y EL HOSPITAL SUB REGIONAL DE ANDAHUAYLAS', '2016-11-14', '2019-11-14', 'Perú'],
            ['054-2016', 'HOSPITAL REGIONAL DE MEDICINA TROPICAL DR. JULIO CESAR DEMARINI CARO" DE LA MERCED', 'Salud', 'CONVENIO ESPECÍFICO DE COOPERACIÓN DOCENTE ASISTENCIAL ENTRE EL HOSPITAL REGIONAL DE MEDICINA TROPICAL "DR. JULIO CESAR DEMARINI CARO" DE LA MERCED Y LA FACULTAD DE MEDICINA HUMANA DE LA UNCP', null, '2019-12-07', 'Perú'],
            ['055-2016', 'NATURAL PRODUCTS NAR VID E.I.R.L.', 'Empresa Nacional', 'CONVENIO DE ASOCIACIÓN ENTRE LA EMPRESA NATURAL PRODUCTS NAR VID EIRL Y LA UNCP PARA LA EJECUCIÓN DEL PROYECTO ACEITES ESENCIALES DE PLANTAS EXÓTICAS DE LOS ANDES PERUANOS, EXTRACCIÓN, PURIFICACIÓN...', null, null, 'Perú'],
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
