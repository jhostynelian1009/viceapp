---
id: K-004
nombre: Privatizar documentos y asegurar conservación
fase: 2
estado: PENDIENTE
---

## Objetivo

Evitar acceso público y pérdida accidental de documentos institucionales.

## Entradas

- `Spec/S02-diagnostico-errores.md`: `ERR-008`–`ERR-010`
- `Spec/S07-modelo-datos.md`
- `Spec/S09-seguridad-privacidad.md`

## Salidas

- Disco privado para nuevas cargas.
- Descarga/preview mediante controlador autorizado.
- Migración segura de archivos existentes.
- Relaciones que no eliminan planificaciones por cascada.
- Inventario y plan de retiro de documentos versionados.

## Restricciones

- Realizar respaldo antes de mover archivos o cambiar FKs.
- No purgar historial Git ni borrar documentos sin autorización expresa.
- No utilizar Google Viewer si obliga a publicar el archivo.

## Procedimiento

1. Inventariar registros, rutas físicas, archivos faltantes y huérfanos.
2. Crear respaldo verificable de BD y documentos.
3. Implementar almacenamiento privado con validación de MIME/tamaño.
4. Migrar rutas de archivos de forma idempotente.
5. Servir descargas solo después de policy.
6. Sustituir cascadas destructivas por conservación/soft delete.
7. Probar acceso autorizado, denegado y URL pública inexistente.

## Criterios de aceptación

- Dado un documento, cuando se solicita su URL pública, entonces no es accesible.
- Dado un usuario autorizado, cuando descarga, entonces obtiene el archivo correcto.
- Dado un usuario ajeno, cuando descarga, entonces recibe 403/404 sin contenido.
- Cuando se desactiva un docente o materia, entonces sus planificaciones y archivos permanecen.
- No se avanzó a K-005.
