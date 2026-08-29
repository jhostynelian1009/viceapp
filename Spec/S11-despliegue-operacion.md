# S11 — Despliegue y operación

## Entornos

- `local`: desarrollo individual.
- `testing`: suite automatizada y datos desechables.
- `staging`: validación funcional con datos ficticios.
- `production`: institución, acceso restringido y respaldos activos.

Cada entorno usa su propio `.env` y base de datos.

## Preflight de producción

- PHP, extensiones, Composer y Node compatibles.
- `APP_ENV=production`, `APP_DEBUG=false`, URL y zona horaria correctas.
- Base de datos y usuario con privilegios mínimos.
- Correo/colas configurados si se utilizan.
- Directorios de almacenamiento con permisos correctos.
- HTTPS obligatorio.
- Cron de Laravel configurado si existen tareas programadas.
- Worker supervisado si existen colas.

## Procedimiento de despliegue

1. Activar mantenimiento cuando corresponda.
2. Respaldar base de datos y documentos.
3. Desplegar código desde una revisión identificable.
4. Ejecutar `composer install --no-dev --optimize-autoloader`.
5. Ejecutar build de frontend reproducible.
6. Ejecutar migraciones con revisión previa.
7. Limpiar y reconstruir cachés.
8. Ejecutar smoke tests.
9. Desactivar mantenimiento y monitorear logs.

## Respaldo y recuperación

- Respaldar base de datos y archivos como una unidad consistente.
- Definir periodicidad, retención y destino con la institución.
- Probar restauración; un respaldo no probado no se considera válido.
- Documentar RPO/RTO cuando exista infraestructura definida.

## Observabilidad

- Monitorear errores 5xx, fallas de colas, espacio de almacenamiento y backups.
- Mantener logs con rotación.
- No registrar secretos ni contenido completo de documentos.

## Rollback

Cada despliegue debe indicar:

- Versión de código anterior.
- Compatibilidad de esquema.
- Procedimiento para restaurar BD y documentos.
- Cambios que no pueden revertirse automáticamente.
