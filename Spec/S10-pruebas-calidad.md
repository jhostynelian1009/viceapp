# S10 — Pruebas y calidad

## Estrategia

La suite se organiza por riesgo, no solo por controlador.

## Pruebas obligatorias

### Autorización

- Docente no puede administrar usuarios ni reportes globales.
- Docente no puede ver, comentar, enviar o descargar planificación ajena.
- Usuario no puede leer o modificar notificación ajena.
- Secretaría y Vicerrectorado solo ejecutan acciones permitidas.
- Secretaría no puede aprobar, rechazar ni alterar estados.
- Vicerrectorado sí puede resolver una planificación pendiente.
- Cuenta inactiva no accede.

### Flujo

- Cada transición válida funciona.
- Cada transición inválida responde 403/422 y no cambia datos.
- Rechazo exige motivo.
- Reentrega genera versión nueva.
- Aprobación registra revisor y fecha.
- Una planificación aprobada no se sobrescribe.

### Archivos

- Se aceptan PDF/DOC/DOCX válidos dentro del límite.
- Se rechazan MIME, extensión o tamaño inválidos.
- Archivo privado no es accesible por URL pública.
- Descarga autorizada funciona y descarga no autorizada falla.
- Eliminar borrador o versión sigue la política de conservación.

### Datos y reportes

- Seeders pueden ejecutarse más de una vez.
- Migración conserva conteos y relaciones.
- El resumen separa aprobadas, pendientes y rechazadas; excluye borradores de pendientes.
- Filtros por área, docente, curso, paralelo, asignatura y semana son correctos.
- Pantalla y exportación contienen el mismo conjunto de registros.

### Interfaz

- Componentes Blade compilan.
- Navegación móvil no contiene componentes mal cerrados.
- Scripts requeridos se cargan.
- Build de producción finaliza correctamente.

## Comandos de calidad

```bash
composer validate
php artisan test
vendor/bin/pint --test
npm ci
npm run build
composer audit
npm audit --omit=dev
```

## Base de datos de pruebas

- Nunca usar la BD de desarrollo o producción.
- SQLite en memoria puede cubrir lógica general.
- Las migraciones que dependan de enums/cambios propios de MySQL requieren una ejecución adicional sobre MySQL/MariaDB de testing.

## Criterio de regresión

Una skill no se cierra con pruebas fallidas. Si existe una falla previa no relacionada, documentarla con evidencia y aislarla; no ocultarla ni desactivar el test.
