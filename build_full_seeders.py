#!/usr/bin/env python3
"""Generate complete seeders matching PDF count per year."""
import os, re, glob, json, subprocess, sys, tempfile, shutil
from datetime import datetime, timedelta
from multiprocessing import Pool, cpu_count
import xml.etree.ElementTree as ET
import zipfile

OCR_ENABLED = True
TESSERACT_BIN = "/tmp/tesseract_local/usr/bin/tesseract"
os.environ['LD_LIBRARY_PATH'] = "/tmp/tesseract_local/usr/lib:" + os.environ.get('LD_LIBRARY_PATH', '')
os.environ['TESSDATA_PREFIX'] = "/tmp/tessdata"

PDF_BASE = "/home/nando/Documentos/Documentos OCRI/Convenios Escaneados hasta 2026"
SEEDER_DIR = "/home/nando/Documentos/OCRI2/database/seeders"
XLSX_PATH = "/home/nando/Documentos/Documentos OCRI/Convenios Escaneados hasta 2026/2025/Exel de convenios/CONVENIOS AL 2025 (5).xlsx"

EXISTING_SEEDERS = {'2019','2020','2021','2022','2023','2024','2025'}

# Load Excel data
ns = {'s': 'http://schemas.openxmlformats.org/spreadsheetml/2006/main'}

def load_excel_data():
    """Load all entries from Excel, keyed by code."""
    excel_entries = {}
    with zipfile.ZipFile(XLSX_PATH) as z:
        ss_tree = ET.parse(z.open('xl/sharedStrings.xml'))
        SS = {}
        for i, si in enumerate(ss_tree.findall('.//s:si', ns)):
            SS[i] = ''.join(si.itertext()).strip()
        
        def cell_val(cell):
            v = cell.find('s:v', ns)
            t = cell.get('t')
            if v is not None:
                val = v.text
                if t == 's':
                    idx = int(val)
                    return ('s', SS.get(idx, ''))
                return ('n', val)
            return None
        
        wb = ET.parse(z.open('xl/workbook.xml'))
        sheet_names = {}
        for i, s in enumerate(wb.findall('.//s:sheet', ns)):
            sheet_names[s.get('name')] = i + 1
        
        def parse_text_date(text):
            meses = {
                'ENERO': '01', 'FEBRERO': '02', 'MARZO': '03', 'ABRIL': '04',
                'MAYO': '05', 'JUNIO': '06', 'JULIO': '07', 'AGOSTO': '08',
                'SETIEMBRE': '09', 'OCTUBRE': '10', 'NOVIEMBRE': '11', 'DICIEMBRE': '12',
                'SEPTIEMBRE': '09'
            }
            t = text.upper().strip()
            for mn, mv in meses.items():
                if mn in t:
                    parts = re.split(r'\s+', t.replace('DE', ' '))
                    day = None; year = None
                    for p in parts:
                        p = p.strip().rstrip('.').strip()
                        if p.isdigit() and len(p) == 4 and 1900 <= int(p) <= 2100:
                            year = p
                        elif p.isdigit() and 1 <= int(p) <= 31:
                            day = p.zfill(2)
                    if day and year:
                        return f"{year}-{mv}-{day}"
            return None
        
        def excel_serial_to_date(serial):
            try:
                s = float(serial)
                if s > 60: s -= 1
                base = datetime(1899, 12, 30)
                return (base + timedelta(days=s)).strftime('%Y-%m-%d')
            except:
                return None
        
        years_old = ['2005','2006','2007','2008','2009','2010','2011','2012',
                     '2013','2014','2015','2016','2017','2018']
        years_new = ['2019','2020','2021','2022','2023','2024','2025']
        
        for year in years_old + years_new:
            if year not in sheet_names:
                continue
            sidx = sheet_names[year]
            ws = ET.parse(z.open(f'xl/worksheets/sheet{sidx}.xml'))
            rows = ws.findall('.//s:row', ns)
            is_old = int(year) <= 2018
            
            # Find first data row
            first_data = None
            for row in rows:
                r = int(row.get('r'))
                cells = row.findall('.//s:c', ns)
                rd = {}
                for cell in cells:
                    cv = cell_val(cell)
                    if cv: rd[cell.get('r')[0]] = cv
                if 'B' in rd and rd['B'][0] == 's':
                    code = rd['B'][1]
                    if year in code or code.endswith(year):
                        first_data = r
                        break
            
            if first_data is None:
                continue
            
            for row in rows:
                r = int(row.get('r'))
                if r < first_data:
                    continue
                cells = row.findall('.//s:c', ns)
                rd = {}
                for cell in cells:
                    cv = cell_val(cell)
                    if cv: rd[cell.get('r')[0]] = cv
                if not rd: continue
                
                code = rd['B'][1] if 'B' in rd and rd['B'][0] == 's' else ''
                if not code or (year not in code and not code.endswith(year)):
                    continue
                
                name = rd['C'][1] if 'C' in rd and rd['C'][0] == 's' else ''
                
                # Institution from col G (old) or extract from name (new)
                institution = ''
                if is_old and 'G' in rd and rd['G'][0] == 's':
                    institution = rd['G'][1]
                else:
                    nu = name.upper()
                    m = re.search(r'ENTRE\s+(.+?)\s+Y\s+(?:EL|LA|LOS|LAS)\s', nu)
                    if m: institution = m.group(1).strip()
                    else: institution = nu[:80]
                
                # Entity type
                es_nacional = True
                if 'H' in rd:
                    hv = rd['H'][1].upper() if rd['H'][0] == 's' else ''
                    if 'EXTRANJERA' in hv or hv in ('X', 'SI'):
                        es_nacional = False
                if 'I' in rd:
                    iv = rd['I'][1].upper() if rd['I'][0] == 's' else ''
                    if iv == 'X':
                        es_nacional = False
                
                country = 'Perú' if es_nacional else 'Internacional'
                
                # Start date
                start_date = None
                date_col = 'K' if is_old else 'L'
                if date_col in rd:
                    if rd[date_col][0] == 's':
                        d = parse_text_date(rd[date_col][1])
                        if d: start_date = d
                    else:
                        d = excel_serial_to_date(rd[date_col][1])
                        if d: start_date = d
                
                # End date
                end_date = None
                end_col = 'L' if is_old else 'M'
                if end_col in rd:
                    if rd[end_col][0] == 's':
                        txt = rd[end_col][1].strip().upper()
                        ignore = {'INDETERMINADO','INDETERMINADA','NO ESPECIFICA','','INDEFINIDO','INDEFINIDA','RENOVACIÓN AUTOMÁTICA','INDETERNINADO'}
                        if txt and txt not in ignore:
                            d = parse_text_date(rd[end_col][1])
                            if d: end_date = d
                    else:
                        d = excel_serial_to_date(rd[end_col][1])
                        if d: end_date = d
                
                excel_entries[code] = {
                    'code': code,
                    'institution': institution,
                    'name': name,
                    'start_date': start_date,
                    'end_date': end_date,
                    'country': country
                }
    
    return excel_entries

def classify_institution(name):
    nu = name.upper()
    if not nu.strip(): return 'Otros'
    if 'UNIVERSIDAD NACIONAL' in nu: return 'Universidad Nacional'
    if 'UNIVERSIDAD' in nu: return 'Educación'
    if 'MUNICIPALIDAD' in nu: return 'Sector Público'
    if 'GOBIERNO' in nu or 'DIRECCION' in nu or 'DIRECCIÓN' in nu or 'MINISTERIO' in nu: return 'Sector Público'
    if 'ESSALUD' in nu or 'HOSPITAL' in nu or 'RED ASISTENCIAL' in nu or 'RED DE SALUD' in nu or 'SALUD' in nu: return 'Salud'
    if 'COMUNIDAD' in nu: return 'Comunidades'
    if 'EMPRESA' in nu or 'SAC' in nu or 'EIRL' in nu or 'SRL' in nu: return 'Empresa Nacional'
    if 'INSTITUTO' in nu or 'IESTP' in nu or 'IESPP' in nu or 'COLEGIO' in nu: return 'Educación'
    return 'Otros'

def ocr_pdf(pdf_path, max_pages=4):
    """OCR a PDF and return text."""
    tmpdir = tempfile.mkdtemp()
    try:
        # Convert PDF pages to images at 200 DPI
        result = subprocess.run(
            ['pdftoppm', '-f', '1', '-l', str(max_pages), '-r', '200', pdf_path, f'{tmpdir}/page'],
            capture_output=True, text=True, timeout=120
        )
        if result.returncode != 0:
            return ""
        
        text_parts = []
        for i in range(1, max_pages+1):
            img = f'{tmpdir}/page-{i}.ppm'
            if os.path.exists(img):
                r = subprocess.run(
                    [TESSERACT_BIN, img, 'stdout', '-l', 'spa'],
                    capture_output=True, text=True, timeout=60
                )
                if r.returncode == 0:
                    text_parts.append(r.stdout)
            else:
                # Try alternative naming
                img = f'{tmpdir}/page-{i:02d}.ppm'
                if os.path.exists(img):
                    r = subprocess.run(
                        [TESSERACT_BIN, img, 'stdout', '-l', 'spa'],
                        capture_output=True, text=True, timeout=60
                    )
                    if r.returncode == 0:
                        text_parts.append(r.stdout)
        return '\n'.join(text_parts)
    except:
        return ""
    finally:
        shutil.rmtree(tmpdir, ignore_errors=True)

def parse_ocr_text(text, code, year):
    """Extract institution, start_date, end_date from OCR text."""
    result = {
        'institution': '',
        'start_date': None,
        'end_date': None,
        'country': 'Perú',
        'name': ''
    }
    
    text_upper = text.upper()
    
    # Find institution name - look for "DE LA OTRA PARTE" or after "Y" before universities
    # Patterns: "DE LA OTRA PARTE: ..." or "Y LA ..." or "CELEBRAN"
    
    # Look for institution after "DE LA OTRA PARTE"
    m = re.search(r'DE\s+LA\s+OTRA\s+PARTE[:\s]+(.+?)(?:,|\.|CON\s+DNI|DEBIDAMENTE)', text_upper)
    if m:
        inst = m.group(1).strip()
        # Remove common prefixes
        for pfx in ['LA ', 'EL ', 'LOS ', 'LAS ']:
            if inst.startswith(pfx):
                inst = inst[len(pfx):]
        result['institution'] = inst[:120]
    else:
        # Try "ENTRE ... Y ..." pattern
        m = re.search(r'ENTRE\s+(?:LA\s+|EL\s+|LAS\s+|LOS\s+)?(.+?)\s+Y\s+(?:LA\s+|EL\s+|LAS\s+|LOS\s+)?(.+?)(?:,|\.|\n)', text_upper)
        if m:
            # Second party is usually the external institution
            inst = m.group(2).strip()
            for pfx in ['LA ', 'EL ', 'LOS ', 'LAS ']:
                if inst.startswith(pfx):
                    inst = inst[len(pfx):]
            result['institution'] = inst[:120]
        else:
            # Try to find any institution name mentioning university or similar
            m = re.search(r'(UNIVERSIDAD\s+(?:NACIONAL\s+)?[A-ZÁÉÍÓÚÑ\s]+)', text_upper)
            if m:
                result['institution'] = m.group(1).strip()[:120]
    
    # If institution name still empty, use code
    if not result['institution']:
        result['institution'] = f'INSTITUCION_{code}'
    
    # Set name from the first line of the document
    lines = [l.strip() for l in text.split('\n') if l.strip()]
    for line in lines[:5]:
        if 'CONVENIO' in line.upper() or 'MEMORANDO' in line.upper() or 'ACUERDO' in line.upper() or 'CARTA' in line.upper():
            result['name'] = line[:200]
            break
    if not result['name']:
        result['name'] = lines[0][:200] if lines else f'CONVENIO {code}'
    
    # Find dates
    # Pattern 1: "NN DE MES DE AAAA"
    meses = {
        'ENERO': '01', 'FEBRERO': '02', 'MARZO': '03', 'ABRIL': '04',
        'MAYO': '05', 'JUNIO': '06', 'JULIO': '07', 'AGOSTO': '08',
        'SETIEMBRE': '09', 'OCTUBRE': '10', 'NOVIEMBRE': '11', 'DICIEMBRE': '12',
        'SEPTIEMBRE': '09'
    }
    
    # Search for date patterns near "VIGENCIA" or "PLAZO" or "DURACIÓN"
    date_contexts = []
    for keyword in ['VIGENCIA', 'PLAZO', 'DURACIÓN', 'DURACION', 'VIGENTE']:
        idx = text_upper.find(keyword)
        if idx >= 0:
            date_contexts.append(text[idx:idx+500])
    
    # Also search whole text for date patterns
    search_text = '\n'.join(date_contexts) if date_contexts else text
    
    date_pattern = re.compile(r'(\d{1,2})\s+DE\s+(' + '|'.join(meses.keys()) + r')\s+DE\s+(\d{4})')
    all_dates = date_pattern.findall(search_text.upper())
    
    if all_dates:
        # First date found is likely start date
        d = all_dates[0]
        result['start_date'] = f"{d[2]}-{meses[d[1]]}-{d[0].zfill(2)}"
        if len(all_dates) > 1:
            d = all_dates[-1]
            result['end_date'] = f"{d[2]}-{meses[d[1]]}-{d[0].zfill(2)}"
    
    # Also look for numeric dates like "08.05.2020" or "08/05/2020"
    if not result['start_date']:
        nd = re.findall(r'(\d{2})[./](\d{2})[./](\d{4})', text)
        if nd:
            d = nd[0]
            result['start_date'] = f"{d[2]}-{d[1]}-{d[0]}"
            if len(nd) > 1:
                d = nd[-1]
                result['end_date'] = f"{d[2]}-{d[1]}-{d[0]}"
    
    # Check if it's an international agreement
    intl_keywords = ['COLOMBIA', 'ECUADOR', 'CHILE', 'ARGENTINA', 'BRASIL', 'MÉXICO', 'MEXICO', 
                     'ESPAÑA', 'ESPANA', 'ESTADOS UNIDOS', 'CANADÁ', 'CANADA', 'ALEMANIA',
                     'FRANCIA', 'ITALIA', 'PORTUGAL', 'REINO UNIDO', 'JAPÓN', 'JAPON', 'CHINA',
                     'INTERNACIONAL', 'EXTRANJERA']
    for kw in intl_keywords:
        if kw in text_upper:
            result['country'] = 'Internacional'
            break
    
    # Check for "INDETERMINADO" or "INDEFINIDO" - these mean no end date
    for kw in ['INDETERMINADO', 'INDEFINIDO', 'NO TIENE FECHA', 'RENOVACIÓN AUTOMÁTICA']:
        if kw in text_upper:
            result['end_date'] = None
            break
    
    return result

def get_year_pdfs():
    """Get all PDF files organized by year."""
    year_pdfs = {}
    
    # Main year directories
    for year in range(2005, 2027):
        dirpath = os.path.join(PDF_BASE, str(year))
        if os.path.isdir(dirpath):
            pdfs = sorted(glob.glob(os.path.join(dirpath, '*.pdf')))
            if pdfs:
                year_pdfs[str(year)] = pdfs
    
    # Extra directories
    extra_dir = os.path.join(PDF_BASE, 'CONVENIOS ESCANEADOS 2020-2023')
    if os.path.isdir(extra_dir):
        for sub in ['2020', '2021', '2022', '2023']:
            dp = os.path.join(extra_dir, sub)
            if os.path.isdir(dp):
                pdfs = sorted(glob.glob(os.path.join(dp, '*.pdf')))
                for pdf in pdfs:
                    fname = os.path.basename(pdf)
                    if sub not in year_pdfs:
                        year_pdfs[sub] = []
                    # Only add if not already present
                    existing_names = {os.path.basename(p) for p in year_pdfs.get(sub, [])}
                    if fname not in existing_names:
                        year_pdfs[sub].append(pdf)
    
    # Lic. Rosario directory - add to their respective years if possible
    rosario_dir = os.path.join(PDF_BASE, 'CONVENIO ESCANEADO- LIC.ROSARIO')
    if os.path.isdir(rosario_dir):
        for pdf in sorted(glob.glob(os.path.join(rosario_dir, '*.pdf'))):
            fname = os.path.basename(pdf)
            # Try to determine year from filename
            m = re.search(r'(\d{4})', fname)
            if m:
                yr = m.group(1)
                if yr in year_pdfs:
                    existing_names = {os.path.basename(p) for p in year_pdfs[yr]}
                    if fname not in existing_names:
                        year_pdfs[yr].append(pdf)
    
    return year_pdfs

def get_code_from_filename(fname):
    """Extract agreement code from PDF filename."""
    # Standard format: NNN-YYYY.pdf
    m = re.match(r'(\d{3})-(\d{4})\.pdf$', fname)
    if m:
        return f"{m.group(1)}-{m.group(2)}"
    # Format: NNN-YYYY NAME.pdf
    m = re.match(r'(\d{3})-(\d{4})\s', fname)
    if m:
        return f"{m.group(1)}-{m.group(2)}"
    # Format: NNN. NAME.pdf (without year)
    m = re.match(r'(\d{3})\.\s', fname)
    if m:
        return f"{m.group(1)}"
    # Just numbers at start
    m = re.match(r'(\d{3})', fname)
    if m:
        return m.group(1)
    return fname.replace('.pdf', '')

def process_year(year, pdfs, excel_entries):
    """Process all PDFs for a year and generate entries."""
    entries = []
    pdfs_done = 0
    
    for pdf_path in pdfs:
        fname = os.path.basename(pdf_path)
        code = get_code_from_filename(fname)
        
        # Also try to construct standard code
        std_code = None
        m = re.match(r'(\d{3})', fname)
        if m:
            std_code = f"{m.group(1)}-{year}"
        
        # Check if we have Excel data for this code
        excel_entry = None
        if code in excel_entries:
            excel_entry = excel_entries[code]
        elif std_code and std_code in excel_entries:
            excel_entry = excel_entries[std_code]
            code = std_code
        elif code in excel_entries:
            pass
        # Also try without leading zeros
        for ek in excel_entries:
            if ek.replace('-0', '-').replace('0', '') == code.replace('0', ''):
                excel_entry = excel_entries[ek]
                code = ek
                break
        
        if excel_entry:
            entry = dict(excel_entry)
            entry['code'] = code
            entries.append(entry)
            pdfs_done += 1
        else:
            # Need OCR
            if OCR_ENABLED and os.path.exists(pdf_path):
                print(f"  OCR: {fname}")
                text = ocr_pdf(pdf_path, max_pages=3)
                parsed = parse_ocr_text(text, code, year)
                entry = {
                    'code': std_code if std_code else code,
                    'institution': parsed['institution'],
                    'inst_type': classify_institution(parsed['institution']),
                    'name': parsed['name'],
                    'start_date': parsed['start_date'],
                    'end_date': parsed['end_date'],
                    'country': parsed['country']
                }
                entries.append(entry)
                pdfs_done += 1
    
    print(f"  {year}: processed {pdfs_done}/{len(pdfs)} PDFs => {len(entries)} entries")
    return entries

def generate_seeder(year, entries):
    """Generate PHP seeder file."""
    if not entries:
        return
    
    class_name = f"Convenios{year}Seeder"
    filename = os.path.join(SEEDER_DIR, f"{class_name}.php")
    
    def esc(s):
        if not s: return "''"
        s = str(s).replace("'", "\\'")
        if len(s) > 200: s = s[:197] + '...'
        return f"'{s}'"
    
    def dt(d):
        return f"'{d}'" if d else 'null'
    
    lines = [
        "<?php\n",
        "namespace Database\\Seeders;\n",
        "use Illuminate\\Database\\Seeder;",
        "use App\\Models\\Agreement;",
        "use App\\Models\\Institution;",
        "use App\\Models\\AgreementType;",
        "use Illuminate\\Support\\Facades\\DB;",
        "use Illuminate\\Support\\Facades\\Storage;",
        "",
        f"class {class_name} extends Seeder",
        "{",
        "    public function run()",
        "    {",
        "        DB::statement('SET FOREIGN_KEY_CHECKS=0;');\n",
        "        $tipos = [",
        "            'marco' => AgreementType::firstOrCreate(['name' => 'Convenio Marco']),",
        "            'especifico' => AgreementType::firstOrCreate(['name' => 'Convenio Específico']),",
        "            'memorando' => AgreementType::firstOrCreate(['name' => 'Memorando de Entendimiento']),",
        "            'adenda' => AgreementType::firstOrCreate(['name' => 'Adenda']),",
        "        ];\n",
        "        $datos = [",
    ]
    
    for e in entries:
        inst_type = classify_institution(e['institution'])
        lines.append(f"            [{esc(e['code'])}, {esc(e['institution'])}, {esc(inst_type)}, {esc(e['name'])}, {dt(e['start_date'])}, {dt(e['end_date'])}, {esc(e['country'])}],")
    
    lines.extend([
        "        ];\n",
        "        foreach ($datos as $fila) {",
        "            $nombreInstitucion = strtoupper($fila[1]);",
        "            $tipoEstandarizado = $fila[2];",
        "            $pais = $fila[6];\n",
        "            $institucion = Institution::firstOrCreate(",
        "                ['name' => $nombreInstitucion],",
        "                ['country' => $pais, 'type' => $tipoEstandarizado]",
        "            );\n",
        "            $nombreLargo = strtoupper($fila[3]);\n",
        "            if (str_contains($nombreLargo, 'MEMORANDO')) {",
        "                $tipoId = $tipos['memorando']->id;",
        "            } elseif (str_contains($nombreLargo, 'ADENDA')) {",
        "                $tipoId = $tipos['adenda']->id;",
        "            } elseif (str_contains($nombreLargo, 'ESPECIFICO') || str_contains($nombreLargo, 'ESPECÍFICO')) {",
        "                $tipoId = $tipos['especifico']->id;",
        "            } else {",
        "                $tipoId = $tipos['marco']->id;",
        "            }\n",
        "            $agreement = Agreement::firstOrCreate(",
        "                ['resolution_number' => $fila[0]],",
        "                [",
        "                'title' => $fila[0],",
        "                'name' => $fila[3],",
        "                'resolution_number' => $fila[0],",
        "                'institution_id' => $institucion->id,",
        "                'agreement_type_id' => $tipoId, ",
        "                'start_date' => $fila[4],",
        "                'end_date' => $fila[5],",
        "                'status' => 'Vigente'",
        "            ]);\n",
        "            $codigo = $fila[0];",
        "            $anio = substr($codigo, -4); ",
        "            $rutaRelativa = \"convenios/{$anio}/{$codigo}.pdf\";\n",
        "            if (Storage::disk('public')->exists($rutaRelativa)) {",
        "                $agreement->documents()->firstOrCreate(",
        "                    ['file_path' => $rutaRelativa],",
        "                    [",
        "                    'name' => 'Doc - ' . ($agreement->resolution_number ?? $agreement->title),",
        "                    'file_path' => $rutaRelativa,",
        "                    'extension' => 'pdf'",
        "                ]);",
        "            }",
        "        }\n",
        "        DB::statement('SET FOREIGN_KEY_CHECKS=1;');",
        "    }",
        "}\n",
    ])
    
    with open(filename, 'w') as f:
        f.write('\n'.join(lines))
    
    print(f"  Generated: {class_name}.php ({len(entries)} entries)")

def update_database_seeder():
    """Update DatabaseSeeder.php to call all seeders."""
    path = os.path.join(SEEDER_DIR, 'DatabaseSeeder.php')
    years = [str(y) for y in range(2005, 2026)]
    calls = '\n'.join(f"            Convenios{y}Seeder::class," for y in years)
    
    content = f"""<?php

namespace Database\\Seeders;

use Illuminate\\Database\\Seeder;

class DatabaseSeeder extends Seeder
{{
    public function run()
    {{
        $this->call([
{calls}
        ]);
    }}
}}
"""
    with open(path, 'w') as f:
        f.write(content)
    print("Updated DatabaseSeeder.php")

if __name__ == '__main__':
    print("Loading Excel data...")
    excel_entries = load_excel_data()
    print(f"  Loaded {len(excel_entries)} Excel entries")
    
    print("\nScanning PDFs...")
    year_pdfs = get_year_pdfs()
    for y in sorted(year_pdfs.keys(), key=int):
        print(f"  {y}: {len(year_pdfs[y])} PDFs")
    print(f"  Total: {sum(len(v) for v in year_pdfs.values())} PDFs")
    
    print("\nProcessing each year...")
    for year in sorted(year_pdfs.keys(), key=int):
        pdfs = year_pdfs[year]
        print(f"\n{year}: {len(pdfs)} PDFs")
        entries = process_year(year, pdfs, excel_entries)
        
        # Sort entries by code
        entries.sort(key=lambda e: e['code'])
        
        generate_seeder(year, entries)
    
    print("\nUpdating DatabaseSeeder.php...")
    update_database_seeder()
    
    print("\nDone!")
