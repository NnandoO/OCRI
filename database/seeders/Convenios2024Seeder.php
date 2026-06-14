<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Agreement;
use App\Models\Institution;
use App\Models\AgreementType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class Convenios2024Seeder extends Seeder
{
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        $tipos = [
            'marco' => AgreementType::firstOrCreate(['name' => 'Convenio Marco']),
            'especifico' => AgreementType::firstOrCreate(['name' => 'Convenio Específico']),
            'memorando' => AgreementType::firstOrCreate(['name' => 'Memorando de Entendimiento']),
        ];
        
        // Estructura: [0: Código/Resolución, 1: Institución, 2: Tipo, 3: Nombre del convenio, 4: Fecha Inicio, 5: Fecha Fin, 6: País]
        $datos = [
            ['001-2024', 'COMUNIDAD CAMPESINA DE HUARI', 'Comunidad Campesina', 'CONVENIO MARCO DE COOPERACION INTERINSTITUCIONAL ENTRE LA UNIVERSIDAD NACIONAL DEL CENTRO DEL PERÚ Y LA COMUNIDAD CAMPESINA DE HUARI', '2024-01-08', '2026-01-08', 'Perú'],
            ['002-2024', 'UNIVERSIDAD NACIONAL DE SAN MARTÍN', 'Universidad Nacional', 'CONVENIO MARCO DE COOPERACION INTERINSTITUCIONAL ENTRE LA UNIVERSIDAD NACIONAL DEL CENTRO DEL PERÚ Y LA UNIVERSIDAD NACIONAL DE SAN MARTÍN', '2024-01-08', '2027-01-08', 'Perú'],
            ['003-2024', 'MUNICIPALIDAD DISTRITAL DE MAZAMARI', 'Municipalidad', 'CONVENIO MARCO DE COOPERACION INTERINSTITUCIONAL ENTRE LA MUNICIPALIDAD DISTRITAL DE MAZAMARI Y LA UNIVERSIDAD NACIONAL DEL CENTRO DEL PERÚ', '2024-01-23', '2027-01-23', 'Perú'],
            ['004-2024', 'GRUPO ASEZ - IGLESIA DE DIOS', 'Asociación', 'CONVENIO MARCO ENTRE LA UNCP Y EL GRUPO DE UNIVERSITARIOS DE LA IGLESIA DE DIOS (ASEZ)', '2024-01-24', '2029-01-24', 'Perú'],
            ['005-2024', 'COMUNIDAD CAMPESINA SANTA ROSA DE TOLDOPAMPA', 'Comunidad Campesina', 'CONVENIO MARCO ENTRE LA UNCP Y LA COMUNIDAD CAMPESINA SANTA ROSA DE TOLDOPAMPA', '2024-02-10', '2027-02-10', 'Perú'],
            ['006-2024', 'COMUNIDAD CAMPESINA DE SAN JUAN DE ONDORES', 'Comunidad Campesina', 'CONVENIO MARCO ENTRE LA UNCP Y LA COMUNIDAD CAMPESINA DE SAN JUAN DE ONDORES', '2024-02-21', '2027-02-21', 'Perú'],
            ['007-2024', 'COMUNIDADES NATIVAS DE PICHANAKI - ACECONAP', 'Comunidad Nativa', 'CONVENIO MARCO ENTRE LA UNCP Y LAS COMUNIDADES NATIVAS DE PICHANAKI', '2024-02-29', '2026-02-29', 'Perú'],
            ['008-2024', 'ASOCIACIÓN NACIÓN PUMPUSH - JUNÍN', 'Asociación', 'CONVENIO MARCO ENTRE LA UNCP Y LA ASOCIACIÓN NACIÓN PUMPUSH DE LA PROVINCIA DE JUNÍN', '2024-02-05', '2027-02-05', 'Perú'],
            ['009-2024', 'CENTRO DE ESTUDIOS PERUANO-CHINO', 'Centro de Estudios', 'CONVENIO MARCO DE COOPERACION ACADEMICA ENTRE LA UNCP Y EL CENTRO DE ESTUDIOS PERUANO-CHINO', '2024-03-22', '2027-03-22', 'Perú'],
            ['010-2024', 'MUNICIPALIDAD CENTRO POBLADO VILLA TINQUERCCASA', 'Municipalidad', 'CONVENIO MARCO ENTRE LA UNCP Y LA MUNICIPALIDAD CP VILLA TINQUERCCASA', '2024-03-20', '2027-03-20', 'Perú'],
            ['011-2024', 'COMUNIDAD CAMPESINA YAURINGA-CONCEPCIÓN', 'Comunidad Campesina', 'CONVENIO MARCO DE COOPERACION INTERINSTITUCIONAL ENTRE LA UNCP Y LA COMUNIDAD CAMPESINA YAURINGA', '2024-04-18', '2026-04-18', 'Perú'],
            ['012-2024', 'COMUNIDAD DE HUANUCO - CONCEPCION', 'Comunidad Campesina', 'CONVENIO MARCO ENTRE LA UNCP Y LA COMUNIDAD DE HUANUCO', '2024-04-19', '2026-04-19', 'Perú'],
            ['013-2024', 'COMUNIDAD DE SANTA ROSA DE RUNATULLO', 'Comunidad Campesina', 'CONVENIO MARCO ENTRE LA UNCP Y LA COMUNIDAD DE SANTA ROSA DE RUNATULLO', '2024-04-19', '2026-04-19', 'Perú'],
            ['014-2024', 'COMUNIDAD DE CAÑA ANDAMAYO', 'Comunidad Campesina', 'CONVENIO MARCO ENTRE LA UNCP Y LA COMUNIDAD DE CAÑA ANDAMAYO', '2024-04-19', '2026-04-19', 'Perú'],
            ['015-2024', 'COMUNIDAD DE PUNCO - CONCEPCIÓN', 'Comunidad Campesina', 'CONVENIO MARCO ENTRE LA UNCP Y LA COMUNIDAD DE PUNCO', '2024-04-19', '2026-04-19', 'Perú'],
            ['016-2024', 'COMUNIDAD DE HUATA - CONCEPCIÓN', 'Comunidad Campesina', 'CONVENIO MARCO ENTRE LA UNCP Y LA COMUNIDAD DE HUATA', '2024-04-19', '2026-04-19', 'Perú'],
            ['017-2024', 'COMUNIDAD DE UYO - CONCEPCION', 'Comunidad Campesina', 'CONVENIO MARCO ENTRE LA UNCP Y LA COMUNIDAD DE UYO', '2024-04-19', '2026-04-19', 'Perú'],
            ['018-2024', 'COMUNIDAD DE ANTACALLA - CONCEPCIÓN', 'Comunidad Campesina', 'CONVENIO MARCO ENTRE LA UNCP Y LA COMUNIDAD DE ANTACALLA', '2024-05-10', '2026-05-10', 'Perú'],
            ['019-2024', 'COMUNIDAD UNIÓN TORREO DE PACAYBAMBA', 'Comunidad Campesina', 'CONVENIO MARCO ENTRE LA UNCP Y LA COMUNIDAD UNIÓN TORREO DE PACAYBAMBA', '2024-05-10', '2026-05-10', 'Perú'],
            ['020-2024', 'COMUNIDAD DE TALHUIS', 'Comunidad Campesina', 'CONVENIO MARCO ENTRE LA UNCP Y LA COMUNIDAD DE TALHUIS', '2024-04-19', '2026-04-19', 'Perú'],
            ['021-2024', 'COMUNIDAD DE PUCACOCHA', 'Comunidad Campesina', 'CONVENIO MARCO ENTRE LA UNCP Y LA COMUNIDAD DE PUCACOCHA', '2024-04-19', '2026-04-19', 'Perú'],
            ['022-2024', 'COMUNIDAD SAN JOSÉ DE CHALLHUA', 'Comunidad Campesina', 'CONVENIO MARCO ENTRE LA UNCP Y LA COMUNIDAD SAN JOSÉ DE CHALLHUA', '2024-05-10', '2026-05-10', 'Perú'],
            ['023-2024', 'UNIVERSIDAD FEDERAL DE MINAS GERAIS (UFMG)', 'Universidad Internacional', 'CONVENIO DE INTERCAMBIO ACADÉMICO ENTRE LA UFMG (BRASIL) Y LA UNCP', '2024-07-05', '2029-07-05', 'Brasil'],
            ['024-2024', 'MUNICIPALIDAD DISTRITAL DE ANCO - HUANCAVELICA', 'Municipalidad', 'CONVENIO MARCO ENTRE LA UNCP Y LA MUNICIPALIDAD DISTRITAL DE ANCO', '2024-03-20', '2027-03-20', 'Perú'],
            ['025-2024', 'SAIS PACHACUTEC', 'Empresa Nacional', 'CONVENIO ESPECÍFICO ENTRE LA UNCP Y LA SAIS PACHACUTEC', '2024-05-29', '2027-05-29', 'Perú'],
            ['026-2024', 'COMUNIDAD CAMPESINA DE ACO', 'Comunidad Campesina', 'CONVENIO MARCO ENTRE LA UNCP Y LA COMUNIDAD CAMPESINA DE ACO', '2024-05-22', '2026-05-22', 'Perú'],
            ['027-2024', 'CLINICA DENTAL PREMIUM EIRL', 'Empresa Nacional', 'CONVENIO DE SERVICIOS ODONTOLOGICOS ENTRE LA UNCP Y CLINICA DENTAL PREMIUM', '2024-05-22', '2026-05-22', 'Perú'],
            ['028-2024', 'COMUNIDAD CAMPESINA DE HUAMANCACA GRANDE', 'Comunidad Campesina', 'CONVENIO MARCO ENTRE LA UNCP Y LA COMUNIDAD CAMPESINA DE HUAMANCACA GRANDE', '2024-05-22', '2026-05-22', 'Perú'],
            ['029-2024', 'MUNICIPALIDAD DISTRITAL DE SAPALLANGA', 'Municipalidad', 'CONVENIO MARCO ENTRE LA UNCP Y LA MUNICIPALIDAD DISTRITAL DE SAPALLANGA', '2024-06-05', '2029-06-05', 'Perú'],
            ['030-2024', 'MUNICIPALIDAD DISTRITAL DE SAPALLANGA', 'Municipalidad', 'CONVENIO ESPECÍFICO ENTRE LA FACULTAD DE ZOOTECNIA UNCP Y MUNICIPALIDAD DE SAPALLANGA', '2024-06-05', '2027-06-05', 'Perú'],
            ['031-2024', 'ESSALUD', 'Salud', 'ALIANZA DE INTERVENCIÓN - PROGRAMA REFORMA DE VIDA UNCP Y ESSALUD', '2024-06-19', '2025-06-19', 'Perú'],
            ['032-2024', 'COMUNIDAD DE HUANCAMACHAY', 'Comunidad Campesina', 'CONVENIO MARCO ENTRE LA UNCP Y LA COMUNIDAD DE HUANCAMACHAY', '2024-06-21', '2026-06-21', 'Perú'],
            ['033-2024', 'BALDE K S.A.C.', 'Empresa Nacional', 'CONVENIO DE COOPERACIÓN INTERINSTITUCIONAL ENTRE LA UNCP Y BALDE K S.A.C.', '2024-06-30', '2025-06-30', 'Perú'],
            ['034-2024', 'COMUNIDAD DE SAN ANTONIO', 'Comunidad Campesina', 'CONVENIO MARCO ENTRE LA UNCP Y LA COMUNIDAD DE SAN ANTONIO', '2024-07-15', '2026-07-15', 'Perú'],
            ['035-2024', 'EGIS VILLE ET TRANSPORTS SUCURSAL DEL PERÚ', 'Empresa Internacional', 'CONVENIO DE COOPERACIÓN Y TRANSFERENCIA DE CONOCIMIENTO ENTRE EVT PERÚ Y LA UNCP', '2024-07-15', '2029-07-15', 'Francia'],
            ['036-2024', 'MUNICIPALIDAD DISTRITAL DE LLAYLLA', 'Municipalidad', 'CONVENIO MARCO ENTRE LA MUNICIPALIDAD DISTRITAL DE LLAYLLA Y LA UNCP', '2024-01-23', '2029-01-23', 'Perú'],
            ['037-2024', 'MUNICIPALIDAD DISTRITAL DE RIO NEGRO', 'Municipalidad', 'CONVENIO MARCO ENTRE LA MUNICIPALIDAD DISTRITAL DE RIO NEGRO Y LA UNCP', '2024-06-01', '2027-06-01', 'Perú'],
            ['038-2024', 'MUNICIPALIDAD DISTRITAL DE RIO TAMBO', 'Municipalidad', 'CONVENIO MARCO ENTRE LA MUNICIPALIDAD DISTRITAL DE RIO TAMBO Y LA UNCP', '2024-06-01', '2027-06-01', 'Perú'],
            ['039-2024', 'MUNICIPALIDAD DISTRITAL DE ACOBAMBILLA', 'Municipalidad', 'CONVENIO MARCO ENTRE LA UNCP Y LA MUNICIPALIDAD DISTRITAL DE ACOBAMBILLA', '2024-07-24', '2026-07-24', 'Perú'],
            ['040-2024', 'EMPRESA COMUNAL SMELTER S.A.', 'Empresa Nacional', 'CONVENIO ESPECÍFICO ENTRE LA FACULTAD DE TRABAJO SOCIAL UNCP Y ECOSEM SMELTER S.A.', '2024-08-09', '2025-08-09', 'Perú'],
            ['041-2024', 'I.E.P. NUESTRA SEÑORA DE FÁTIMA', 'Institución Educativa', 'CONVENIO ESPECIFICO ENTRE LA FACULTAD DE TRABAJO SOCIAL UNCP Y LA I.E.P. NUESTRA SEÑORA DE FÁTIMA', '2024-08-09', '2027-08-09', 'Perú'],
            ['042-2024', 'CENTRO DE SALUD DE HUAYUCACHI', 'Salud', 'CONVENIO ESPECIFICO ENTRE LA FACULTAD DE TRABAJO SOCIAL UNCP Y EL CENTRO DE SALUD DE HUAYUCACHI', '2024-08-09', '2027-08-09', 'Perú'],
            ['043-2024', 'IESTP SANTIAGO ANTUNEZ DE MAYOLO', 'Institución Educativa', 'CONVENIO ESPECIFICO ENTRE LA FACULTAD DE ZOOTECNIA UNCP Y EL IESTP SAM DE PALIAN', '2024-08-12', '2027-08-12', 'Perú'],
            ['044-2024', 'UNIVERSIDAD PERUANA LOS ANDES - UPLA', 'Universidad Privada', 'CONVENIO ESPECIFICO ENTRE LA UNCP Y LA UPLA PARA EL VI CONGRESO DE DEFESORÍAS', '2024-08-23', '2024-11-23', 'Perú'],
            ['045-2024', 'GOBIERNO REGIONAL DE JUNIN', 'Gobierno Regional', 'CONVENIO ESPECIFICO GORE JUNIN Y UNCP - PROYECTO CIENCIAS SOCIALES', '2024-08-22', '2026-08-22', 'Perú'],
            ['046-2024', 'GOBIERNO REGIONAL DE JUNIN', 'Gobierno Regional', 'CONVENIO ESPECIFICO GORE JUNIN Y UNCP - PROYECTO ECONOMÍA', '2024-08-22', '2026-08-22', 'Perú'],
            ['047-2024', 'EMPRESA ELECTROCENTRO S.A.', 'Empresa Nacional', 'CONVENIO ESPECIFICO ENTRE LA FIEE UNCP Y ELECTROCENTRO S.A.', '2024-09-04', '2024-09-04', 'Perú'],
            ['048-2024', 'CORPORACIÓN UNIVERSITARIA REMINGTON', 'Universidad Internacional', 'ACUERDO MARCO DE COOPERACIÓN ACADÉMICA ENTRE UNIREMINGTON Y LA UNCP', '2024-09-05', '2029-09-05', 'Colombia'],
            ['049-2024', 'UNIVERSIDAD NACIONAL AUTONOMA DE HUANTA', 'Universidad Nacional', 'CONVENIO MARCO DE COOPERACIÓN INTERINSTITUCIONAL ENTRE LA UNAH Y LA UNCP', '2024-09-06', '2029-09-06', 'Perú'],
            ['050-2024', 'COMUNIDAD CAMPESINA DE HUAYHUAY', 'Comunidad Campesina', 'CONVENIO MARCO ENTRE LA UNCP Y LA COMUNIDAD CAMPESINA DE HUAYHUAY', '2024-09-24', '2027-09-24', 'Perú'],
            ['051-2024', 'GRUPO DESARROLLO RURAL SOSTENIBLE', 'Asociación', 'CONVENIO MARCO ENTRE LA UNCP Y EL GRUPO DE INTERES DESARROLLO RURAL SOSTENIBLE', '2024-09-26', '2029-09-26', 'Perú'],
            ['052-2024', 'EESP TEODORO PEÑALOZA - CHUPACA', 'Institución Educativa', 'CONVENIO ESPECIFICO ENTRE LA FACULTAD DE TRABAJO SOCIAL UNCP Y LA EESP TEODORO PEÑALOZA', '2024-10-02', '2027-10-02', 'Perú'],
            ['053-2024', 'UNIVERSIDAD HISU DE HEBEI - CHINA', 'Universidad Internacional', 'CONVENIO ESPECIFICO DE COOPERACION ENTRE LA UNCP Y LA UNIVERSIDAD HISU', '2024-10-04', '2027-10-04', 'China'],
            ['054-2024', 'MUNICIPALIDAD DISTRITAL DE HUASICANCHA', 'Municipalidad', 'CONVENIO MARCO ENTRE LA UNCP Y LA MUNICIPALIDAD DISTRITAL DE HUASICANCHA', '2024-10-09', '2026-10-09', 'Perú'],
            ['055-2024', 'MUNICIPALIDAD DISTRITAL DE AHUAC', 'Municipalidad', 'CONVENIO MARCO ENTRE LA UNCP Y LA MUNICIPALIDAD DISTRITAL DE AHUAC', '2024-10-21', '2027-10-09', 'Perú'],
            ['056-2024', 'COMUNIDAD CAMPESINA DE SAÑO', 'Comunidad Campesina', 'CONVENIO MARCO ENTRE LA UNCP Y LA COMUNIDAD CAMPESINA DE SAÑO', '2024-10-21', '2026-10-21', 'Perú'],
            ['057-2024', 'HACIENDA ROMA OQUORS S.A.C.', 'Empresa Nacional', 'CONVENIO ESPECIFICO DE COOPERACIÓN INTERINSTITUCIONAL ENTRE LA UNCP Y HACIENDA ROMA', '2024-11-11', '2027-11-11', 'Perú'],
            ['058-2024', 'MUNICIPALIDAD DISTRITAL DE MARCAPOMACOCHA', 'Municipalidad', 'CONVENIO TRIPARTITO MUNICIPALIDAD, UNCP Y SAIS PACHACUTEC', '2024-12-30', '2026-12-30', 'Perú'],
        ];

        foreach ($datos as $fila) {
            
            // --- INICIO DE LA MODIFICACIÓN ---
            $nombreInstitucion = $fila[1];
            $tipoInstitucion = $fila[2];

            // Si es una comunidad, sobreescribimos el nombre y unificamos el tipo
            if (in_array($tipoInstitucion, ['Comunidad Campesina', 'Comunidad Nativa'])) {
                $nombreInstitucion = 'COMUNIDADES CAMPESINAS Y NATIVAS';
                $tipoInstitucion   = 'Comunidad'; 
            }
            // --- FIN DE LA MODIFICACIÓN ---

            $institucion = Institution::firstOrCreate(
                ['name' => $nombreInstitucion],
                ['type' => $tipoInstitucion, 'country' => $fila[6]]
            );

            $nombreLargo = strtoupper($fila[3]);
            if (str_contains($nombreLargo, 'MEMORANDO')) {
                $tipoId = $tipos['memorando']->id;
            } elseif (str_contains($nombreLargo, 'ESPECIFICO') || str_contains($nombreLargo, 'ESPECÍFICO')) {
                $tipoId = $tipos['especifico']->id;
            } else {
                $tipoId = $tipos['marco']->id;
            }

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