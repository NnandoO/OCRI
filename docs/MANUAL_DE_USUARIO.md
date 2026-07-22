# MANUAL DE USUARIO — SISTEMA OCRI UNCP

**Sistema de Gestión de Convenios de Cooperación y Relaciones Internacionales**
Universidad Nacional del Centro del Perú

---

## 1. ACCESO AL SISTEMA

1. Abrir el navegador y entrar a la URL del sistema.
2. Ingresar con correo electrónico y contraseña.
3. Si es la primera vez, usar las credenciales proporcionadas por el administrador.

---

## 2. DASHBOARD (PÁGINA PRINCIPAL)

Al iniciar sesión se muestra el tablero con:

- **3 tarjetas de estadísticas:** Convenios Vigentes, Prontos a Vencer (próximos 90 días), Vencidos.
- **Tabla de Convenios Recientes:** los últimos 10 registrados.
- **Acciones Rápidas:** botones para Nuevo Convenio, Seguimiento, Reportes.

---

## 3. GESTIÓN DE CONVENIOS

### 3.1 Registro de un Nuevo Convenio

1. Click en **"Nuevo Registro"** (o desde Dashboard → "Nuevo Convenio").
2. Completar los campos:

| Campo | Descripción |
|-------|-------------|
| N° de Convenio | Código único auto-generado (XXX-AÑO). Se puede modificar. |
| Nombre Oficial | Nombre completo del convenio (en mayúsculas automático). |
| Título Corto | Versión resumida para listados. |
| Institución | Buscar o crear nueva institución (click en "➕" para crear rápido). |
| Tipo de Convenio | Marco, Específico, Adenda, etc. |
| Dictamen / Documento Original | PDF opcional del dictamen. |
| Documento del Convenio | PDF del convenio firmado (obligatorio si el estado será Vigente). |
| Fecha de Inicio / Fin | Solo si se sube el documento firmado. |

3. Click en **"Registrar Convenio"**.

### 3.2 Listado y Búsqueda

- **Buscar:** por nombre, resolución, institución o país.
- **Filtros de estado:** Todos / En Proceso / Vigente / Por Vencer / Vencido.
- **Columna N° de Convenio:** al pasar el mouse muestra las opiniones pendientes (tooltip).
- **Acciones por fila:** 👁 Ver detalle | ✏ Editar.

### 3.3 Editar Convenio

1. Click en el ícono de lápiz ✏ en la lista.
2. Modificar los campos necesarios.
3. Click en **"Actualizar Convenio"**.

### 3.4 Ver Detalle del Convenio

Pantalla principal con dos columnas:

**Columna izquierda (Hoja de Ruta):**
- Muestra las áreas seleccionadas para el flujo de opiniones.
- Cada área tiene:
  - **Entrada:** subir documentos recibidos de esa área.
  - **Salida:** subir documentos de respuesta emitidos.
  - **Envío:** seleccionar "Correo" o "ADESA" (con número de expediente).
- Estado visual: ✅ círculo verde si tiene entrada y salida.

**Columna derecha:**
- **Visor de PDF:** previsualiza cualquier documento al hacer click en "Ver".
- **Información:** tipo, resolución, país, dictamen.
- **Acervo Digital:** documentos finales del convenio.
- **Nota de Gestión:** campo de texto para observaciones internas.

### 3.5 Activación de Convenio

Cuando todas las áreas tienen entrada y salida:
1. Aparece un formulario verde.
2. Ingresar: N° de Resolución, Fecha de Suscripción, Fecha de Inicio, Fecha de Fin.
3. Opcional: subir PDF del convenio firmado.
4. Click en **"Activar Convenio"** → el estado cambia a "Vigente".

---

## 4. GENERACIÓN DE OFICIOS

### 4.1 Oficios de Opinión

1. En la pantalla de detalle, click en **"Crear Oficios"**.
2. Para cada área (excepto Rectorado):
   - **Dirigido a:** nombre y cargo del destinatario.
   - **N° de Oficio:** número correlativo (se auto-incrementa al modificar el primero).
3. Click en **"Generar Oficios"**.

### 4.2 Expediente Final a Rectorado

1. Una vez que todas las áreas tienen entrada + salida y los oficios están generados.
2. Click en **"Generar Expediente Final a Rectorado"**.
3. Completar: destinatario y número de oficio.
4. El sistema genera un PDF que incluye:
   - Carta de solicitud de suscripción.
   - Todos los documentos de opinión fusionados (entradas y salidas por área).
5. Se guarda automáticamente en el Acervo Digital.

---

## 5. CONTROL DE ASISTENCIA

Módulo para registrar entrada y salida de practicantes.

### 5.1 Marcar Entrada

1. Ir a **"Control de Asistencia"** en el menú lateral.
2. Escribir el nombre del practicante (se convierte a mayúsculas automáticamente).
3. Click en **"Marcar Entrada"**.

### 5.2 Marcar Salida

1. En la tabla del día, buscar el registro del practicante.
2. Click en **"Salida"** (botón verde).

### 5.3 Consultar por Fecha

- Usar el selector de fecha para ver registros de días anteriores.
- Los días con registro aparecen como enlaces en la barra inferior.

---

## 6. INSTITUCIONES (ALIADOS)

- **Listado:** tarjetas con nombre, país, tipo y cantidad de convenios.
- **Crear:** formulario con nombre, país (selector o texto libre), tipo.
- **Ver Detalle:** muestra los convenios asociados a esa institución.

---

## 7. REPORTES

1. Ir a **"Reportes y Estadísticas"**.
2. Filtrar por: búsqueda general, clasificación, tipo, país, año.
3. Click en **"Filtrar"**.
4. Click en **"Excel"** para descargar los resultados como archivo de Excel.

---

## 8. CONSEJOS ÚTILES

- ➕ Para crear una institución rápida desde el formulario de convenio, click en el botón "➕" al lado del selector.
- 📄 Los PDFs subidos deben ser archivos PDF válidos (máx. 10 MB).
- 🔍 Los filtros de estado se combinan con la búsqueda por texto.
- 🖨 Los oficios generados se descargan desde la lista de oficios o desde la pantalla de detalle.
- ⚠ No eliminar registros de asistencia a menos que sea necesario (no tienen respaldo).

---

## 9. SOLUCIÓN DE PROBLEMAS

| Problema | Causa | Solución |
|----------|-------|----------|
| No carga el CSS | Caché del navegador | Ctrl+F5 o limpiar caché |
| Error 404 al guardar | Sesión expirada | Volver a iniciar sesión |
| No aparecen áreas en hoja de ruta | No se configuraron al crear | Ir a "Editar" o configurar desde el detalle |
| El PDF no se ve en el visor | Archivo corrupto o muy grande | Re-subir el archivo |
| Error "Ruta no definida" | Caché de rutas | El administrador debe ejecutar `php artisan route:clear` |

---

*Documento generado el {{ date('d/m/Y') }} — Sistema OCRI UNCP v1.0*
