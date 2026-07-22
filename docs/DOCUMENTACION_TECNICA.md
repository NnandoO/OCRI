# DOCUMENTACIÓN TÉCNICA — SISTEMA OCRI UNCP

**Versión:** 1.0  
**Framework:** Laravel 13.x / Livewire 4.x / Flux UI 2.x / Tailwind CSS 4.x  
**Base de datos:** MySQL / MariaDB / SQLite  
**PHP:** ^8.3

---

## 1. ARQUITECTURA

### 1.1 Stack Tecnológico

| Capa | Tecnología |
|------|-----------|
| Backend | Laravel 13.x (PHP 8.3+) |
| Frontend | Blade + Livewire 4.x + Alpine.js |
| UI Kit | Flux UI 2.x (Livewire component library) |
| CSS | Tailwind CSS v4 (vía `@tailwindcss/vite`) |
| Build | Vite 8.x + Laravel Vite Plugin |
| Auth | Laravel Fortify (login, 2FA, verificación email) |
| PDF | setasign/fpdf 1.9 + setasign/fpdi 2.6 + Ghostscript |
| Excel | maatwebsite/excel 3.1 |
| BD Driver | MySQL (producción), SQLite (desarrollo) |

### 1.2 Estructura de Directorios

```
/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       ├── AgreementController.php   # CRUD convenios + hoja de ruta + documentos
│   │       ├── AsistenciaController.php   # Control de asistencia
│   │       ├── InstitutionController.php  # CRUD instituciones
│   │       ├── OficioController.php       # Generación de oficios y expediente final
│   │       ├── ReportController.php       # Reportes con filtros y exportación Excel
│   │       └── Controller.php             # Clase base abstracta
│   ├── Models/
│   │   ├── Agreement.php                  # Convenio (fillable, casts, mutators, relations)
│   │   ├── AgreementType.php              # Tipo de convenio
│   │   ├── Asistencia.php                 # Asistencia (tabla independiente)
│   │   ├── Document.php                   # Documento principal del convenio
│   │   ├── Institution.php                # Institución (con mutator type())
│   │   ├── Oficio.php                     # Oficio generado (opinión/final)
│   │   ├── RoadmapDocument.php            # Documento de hoja de ruta (entrada/salida)
│   │   ├── RoadmapItem.php                # Área de hoja de ruta
│   │   └── User.php                       # Usuario (Fortify + 2FA)
│   └── Services/
│       └── OficioGeneratorService.php     # Generación de PDFs (FPDF + Ghostscript)
├── config/
│   ├── app.php                            # Timezone: America/Lima, locale: es
│   └── database.php                       # Conexiones BD
├── database/
│   └── migrations/                        # 20 migraciones
│   └── seeders/                           # DatabaseSeeder + UserSeeder + Convenios*Seeder
├── resources/
│   ├── css/app.css                        # Tailwind v4 + Flux + tema personalizado
│   ├── views/
│   │   ├── agreements/                    # CRUD + hoja de ruta + oficios
│   │   ├── asistencia/                    # Control de asistencia
│   │   ├── institutions/                  # CRUD instituciones
│   │   ├── reports/                       # Reportes
│   │   ├── layouts/                       # Sidebar, Header, Auth layouts
│   │   ├── components/                    # Componentes reutilizables
│   │   ├── partials/                      # Head, settings
│   │   └── pages/                         # Auth, settings
│   └── js/app.js                          # Entry point JS
├── routes/web.php                         # 30+ rutas protegidas con auth
├── vendor/                                # Dependencias Composer
├── public/
│   ├── build/                             # Assets compilados por Vite
│   ├── storage/ → storage/app/public      # Symlink
│   ├── Logo-UNCP.png                      # Logo UNCP (PDF)
│   ├── ocri_logo.png                      # Logo OCRI (PDF)
│   └── firma.png                          # Firma escaneada (PDF)
└── docs/
    ├── MANUAL_DE_USUARIO.md
    └── DOCUMENTACION_TECNICA.md
```

---

## 2. MODELO DE DATOS

### 2.1 Diagrama de Entidades

```
User ────────────────────────────────── (autenticación, sin FK)
  │
Institution (1) ────── (N) Agreement (N) ────── (1) AgreementType
                              │
                         (1) ── (N) Document        [acervo digital]
                         (1) ── (N) RoadmapItem     [hoja de ruta]
                                    │
                              (1) ── (N) RoadmapDocument  [PDFs entrada/salida]
                              (1) ── (N) Oficio      [oficios generados]

Asistencia (tabla independiente)
```

### 2.2 Tablas Principales

#### `agreements`

| Columna | Tipo | Restricciones |
|---------|------|---------------|
| id | BIGINT PK | Auto-increment |
| title | VARCHAR(255) | Uppercase mutator |
| name | TEXT | Uppercase mutator |
| resolution_number | VARCHAR(100) | UNIQUE, nullable |
| institution_id | BIGINT FK | → institutions.id |
| agreement_type_id | BIGINT FK | → agreement_types.id |
| start_date | DATE | Nullable |
| end_date | DATE | Nullable |
| status | VARCHAR(50) | Default: "Vigente" |
| situation | TEXT | Nullable, notas internas |
| dictamen_path | VARCHAR(255) | Nullable |
| dictamen_original_name | VARCHAR(255) | Nullable |
| created_at / updated_at | TIMESTAMP | |
| INDEX | status, start_date, end_date | |

#### `roadmap_items`

| Columna | Tipo | Restricciones |
|---------|------|---------------|
| id | BIGINT PK | |
| agreement_id | BIGINT FK | → agreements.id |
| area_name | VARCHAR(255) | |
| is_completed | BOOLEAN | Default false |
| order | INTEGER | Orden de visualización |
| envio_tipo | VARCHAR(20) | Nullable, "correo" \| "adesa" |
| numero_expediente | VARCHAR(100) | Nullable |

#### `roadmap_documents`

| Columna | Tipo | Restricciones |
|---------|------|---------------|
| id | BIGINT PK | |
| roadmap_item_id | BIGINT FK | → roadmap_items.id |
| file_path | VARCHAR(255) | Ruta relativa en storage |
| original_name | VARCHAR(255) | Nombre original del archivo |
| type | VARCHAR(20) | "entrada" \| "salida" |

#### `oficios`

| Columna | Tipo | Restricciones |
|---------|------|---------------|
| id | BIGINT PK | |
| agreement_id | BIGINT FK | → agreements.id |
| roadmap_item_id | BIGINT FK | Nullable, → roadmap_items.id |
| area_name | VARCHAR(255) | |
| directed_to | VARCHAR(500) | Destinatario |
| oficio_number | VARCHAR(100) | N° correlativo |
| file_path | VARCHAR(255) | Ruta del PDF generado |
| file_original_name | VARCHAR(255) | |
| type | ENUM('opinion','final') | |

#### `asistencia`

| Columna | Tipo | Restricciones |
|---------|------|---------------|
| id | BIGINT PK | |
| nombre | VARCHAR(255) | Uppercase |
| fecha | DATE | INDEX |
| hora_entrada | TIME | Nullable |
| hora_salida | TIME | Nullable |
| INDEX | nombre, fecha | |

---

## 3. RUTAS (API INTERNA)

| Método | URI | Controlador@método | Nombre |
|--------|-----|-------------------|--------|
| GET | `/dashboard` | AgreementController@dashboard | dashboard |
| GET | `/agreements` | AgreementController@index | agreements.index |
| GET | `/agreements/create` | AgreementController@create | agreements.create |
| POST | `/agreements` | AgreementController@store | agreements.store |
| GET | `/agreements/{agreement}` | AgreementController@show | agreements.show |
| GET | `/agreements/{agreement}/edit` | AgreementController@edit | agreements.edit |
| PUT | `/agreements/{agreement}` | AgreementController@update | agreements.update |
| DELETE | `/agreements/{agreement}` | AgreementController@destroy | agreements.destroy |
| PATCH | `/agreements/{agreement}/status` | AgreementController@updateStatus | agreements.updateStatus |
| PATCH | `/agreements/{agreement}/activate` | AgreementController@activate | agreements.activate |
| POST | `/agreements/{agreement}/roadmap` | AgreementController@storeRoadmap | agreements.roadmap.store |
| PATCH | `/agreements/roadmap/{item}/envio` | AgreementController@updateEnvio | agreements.roadmap.envio |
| PATCH | `/agreements/roadmap/{itemId}/check` | AgreementController@checkRoadmapItem | agreements.roadmap.check |
| POST | `/agreements/roadmap/{item}/upload` | AgreementController@uploadDocument | agreements.roadmap.upload |
| DELETE | `/agreements/roadmap/document/{document}` | AgreementController@deleteDocument | agreements.roadmap.delete-doc |
| POST | `/agreements/{agreement}/upload-main` | AgreementController@uploadMainDocument | agreements.upload-main |
| DELETE | `/documents/{id}` | AgreementController@destroyMainDocument | documents.destroy |
| GET | `/agreements/{agreement}/oficios` | OficioController@create | agreements.oficios.create |
| POST | `/agreements/{agreement}/oficios` | OficioController@store | agreements.oficios.store |
| POST | `/agreements/{agreement}/expediente-final` | OficioController@generateExpedienteFinal | agreements.expediente-final |
| GET | `/oficios/{oficio}/download` | OficioController@download | oficios.download |
| POST | `/api/institutions` | InstitutionController@store | — (AJAX) |
| GET | `/institutions` | InstitutionController@index | institutions.index |
| GET | `/institutions/create` | InstitutionController@create | institutions.create |
| POST | `/institutions` | InstitutionController@store | institutions.store |
| GET | `/institutions/{institution}` | InstitutionController@show | institutions.show |
| DELETE | `/institutions/{institution}` | InstitutionController@destroy | institutions.destroy |
| GET | `/reports` | ReportController@index | reports.index |
| GET | `/asistencia` | AsistenciaController@index | asistencia.index |
| POST | `/asistencia` | AsistenciaController@store | asistencia.store |
| PATCH | `/asistencia/{asistencia}/salida` | AsistenciaController@marcarSalida | asistencia.salida |
| DELETE | `/asistencia/{asistencia}` | AsistenciaController@destroy | asistencia.destroy |

Todas las rutas están protegidas por middleware `auth` + `verified`.

---

## 4. FLUJO DE TRABAJO (WORKFLOW)

```
[Crear Convenio] → [Configurar Hoja de Ruta] → [Generar Oficios de Opinión]
                                                      ↓
                                            [Subir Entrada/Salida]
                                            (por cada área)
                                                      ↓
                                    [Todas las áreas completas?]
                                       ↓ Sí              ↓ No
                              [Activar Convenio]    [Esperar documentos]
                                       ↓
                              [Estado: Vigente]
                                       ↓
                              [Generar Expediente Final]
                              (Opcional, a Rectorado)
```

---

## 5. GENERACIÓN DE PDFs

### 5.1 Oficios de Opinión

- **Clase:** `OficioGeneratorService::generateOpinionOficio()`
- **Tecnología:** FPDI (extiende FPDF)
- **Output:** `storage/app/public/oficios/{agreement_id}/OFICIO_N_{numero}_OCRI_UNCP.pdf`
- **Encoding:** UTF-8 → ISO-8859-1 (vía `iconv` con `//TRANSLIT//IGNORE`)
- **Logo izquierdo:** `public/Logo-UNCP.png`
- **Logo derecho:** `public/ocri_logo.png`
- **Firma:** `public/firma.png` (50mm ancho, centrada)
- **Footer:** "c.c. Archivo" con `SetAutoPageBreak(false)`

### 5.2 Expediente Final (Rectorado)

- **Clase:** `OficioGeneratorService::generateExpedienteFinal()`
- **Proceso:**
  1. Generar carátula (oficio de solicitud) como PDF temporal.
  2. Fusionar carátula + todos los documentos de opinión (entrada + salida) con **Ghostscript**.
  3. Guardar resultado en `storage/app/public/oficios/{id}/OFICIO N° {num} OCRI UNCP - EXPEDIENTE FINAL RECTORADO.pdf`.
- **Comando Ghostscript:** `gs -dBATCH -dNOPAUSE -q -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -sOutputFile=...`

---

## 6. ALMACENAMIENTO

| Tipo | Ruta | Disco |
|------|------|-------|
| Documentos subidos | `storage/app/public/resoluciones/*` | public |
| Oficios generados | `storage/app/public/oficios/{agreement_id}/*` | public |
| Logos (UNCP, OCRI) | `public/Logo-UNCP.png`, `public/ocri_logo.png` | public |
| Firma escaneada | `public/firma.png` | public |
| Assets compilados | `public/build/assets/app-*.css` | — |

**Symlink requerido:** `public/storage → storage/app/public`

---

## 7. PERSONALIZACIÓN

### 7.1 Temas y Colores

Los colores se definen en `resources/css/app.css` mediante el bloque `@theme`:

| Variable | Uso |
|----------|-----|
| `--color-zinc-*` | Grises del sistema |
| `--color-green-*` | Verde institucional UNCP (sidebar, badges) |
| `--color-yellow-*` | Acento (advertencias, dark mode) |
| `--color-accent` | Color de acento (inputs, focus ring) |

### 7.2 Año Oficial Peruano

Los años denominados se configuran en `OficioGeneratorService::getYearName()`. Agregar nuevos años al array `$names`.

### 7.3 Áreas Predeterminadas

Las áreas que se preseleccionan al crear un convenio están en `resources/views/agreements/show.blade.php`:

```php
$defaultAreas = ['Vicerrectorado de Investigación', 'Vicerrectorado Académico', 'Asesoría Legal', 'CEPRE', 'Rectorado', 'CEID'];
$preselected = ['Vicerrectorado de Investigación', 'Vicerrectorado Académico', 'Rectorado', 'Asesoría Legal'];
```

---

## 8. COMANDOS ÚTILES

### Mantenimiento

```bash
php artisan migrate --force              # Correr migraciones pendientes
php artisan migrate:fresh --seed --force  # Resetear BD + seeders
php artisan route:clear                  # Limpiar caché de rutas
php artisan view:clear                   # Limpiar caché de vistas
php artisan optimize:clear               # Limpiar toda la caché
php artisan storage:link                 # Crear symlink public/storage
```

### Assets

```bash
npm install && npm run build             # Compilar assets para producción
npm run dev                              # Desarrollo con hot-reload
```

### Seeders

```bash
php artisan db:seed --class=UserSeeder --force    # Crear usuario admin
php artisan db:seed --force                        # Todos los seeders
```

---

## 9. DEPENDENCIAS

### Composer (PHP)

| Paquete | Versión | Propósito |
|---------|---------|-----------|
| laravel/framework | ^13.0 | Framework core |
| livewire/livewire | ^4.1 | Componentes dinámicos |
| livewire/flux | ^2.12 | UI components (sidebar, tables, modals) |
| laravel/fortify | ^1.34 | Autenticación (login, 2FA, verificación) |
| maatwebsite/excel | ^3.1 | Exportación a Excel |
| setasign/fpdf | ^1.9 | Generación de PDFs |
| setasign/fpdi | ^2.6 | Importación de PDFs (FPDI) |

### NPM (Frontend)

| Paquete | Versión | Propósito |
|---------|---------|-----------|
| tailwindcss | ^4.0 | CSS utility framework |
| @tailwindcss/vite | ^4.1 | Vite plugin para Tailwind v4 |
| vite | ^8.0 | Build tool |
| laravel-vite-plugin | ^3.0 | Integración Laravel + Vite |

---

## 10. SEGURIDAD

- **Autenticación:** Laravel Fortify con verificación de email y 2FA opcional.
- **Autorización:** No implementa policies/roles (todos los usuarios autenticados tienen acceso completo).
- **CSRF:** Todas las rutas POST/PUT/PATCH/DELETE protegidas por CSRF.
- **XSS:** Todas las salidas Blade usan `{{ }}` (escapado). Las variables en Alpine.js usan `@js()`.
- **Validación:** Todos los inputs validados con reglas de Laravel.
- **Archivos:** Solo PDF permitidos, máximo 10 MB.
- **Middleware:** Todas las rutas funcionales dentro de `auth` + `verified`.

---

## 11. PRUEBAS

```bash
php artisan test                    # Ejecutar tests PHPUnit
php artisan test --filter=Agreement # Tests específicos
```

---

## 12. DESPLIEGUE

### Requisitos del Servidor

- PHP 8.3+
- Composer 2.x
- Node.js 20+ / npm
- MySQL 8+ o MariaDB 10+
- Ghostscript (gs) — requerido para fusión de PDFs
- Extensión PHP: gd, zlib, pdo_mysql, mbstring, iconv

### Pasos

```bash
git clone <repo> /var/www/ocri2
cd /var/www/ocri2
composer install --no-dev --optimize-autoloader
npm install && npm run build
cp .env.example .env
php artisan key:generate
# Configurar .env (BD, mail, etc.)
php artisan migrate --force
php artisan db:seed --class=UserSeeder --force
php artisan storage:link
php artisan route:cache
```

---

*Documento generado el {{ date('d/m/Y') }} — Sistema OCRI UNCP v1.0*
