# S09 — Seguridad, privacidad y conservación documental

## Controles obligatorios

- `SEC-001`: Deshabilitar registro público o restringirlo a invitaciones verificadas.
- `SEC-002`: Aplicar policies a planificaciones, comentarios, usuarios, reportes y notificaciones.
- `SEC-003`: Almacenar archivos fuera de `public/` y servirlos mediante respuestas autorizadas.
- `SEC-004`: Validar extensión, MIME real, tamaño y nombre; generar nombre interno aleatorio.
- `SEC-005`: No mostrar detalles de excepciones externas a usuarios.
- `SEC-006`: Mantener deshabilitada la integración OAuth/Drive durante el MVP.
- `SEC-007`: Si Drive se reintroduce, validar `state`, usar el menor alcance y no escribir tokens en logs.
- `SEC-008`: Evitar asignación masiva con `$request->all()` en operaciones sensibles.
- `SEC-009`: Limitar intentos de autenticación y operaciones susceptibles de abuso.
- `SEC-010`: Añadir cabeceras seguras compatibles con la aplicación.
- `SEC-011`: Mantener `APP_DEBUG=false` y cookies seguras en producción.
- `SEC-012`: Registrar acciones críticas sin guardar contraseñas, tokens o documentos completos.

## Acceso documental

- El propietario y revisores dentro de su ámbito pueden descargar.
- Un documento aprobado conserva accesibilidad histórica según rol.
- Las URLs temporales, si se usan, deben expirar.
- Google Docs Viewer no se utilizará para documentos privados si exige una URL públicamente accesible.
- El preview DOCX debe procesarse de forma local/controlada o degradar a descarga.

## Conservación

- Las planificaciones y versiones no se borran por eliminar usuarios o catálogos.
- La eliminación física será una operación extraordinaria, documentada y autorizada.
- Definir con la institución el tiempo de retención.
- Los respaldos deben cifrarse o almacenarse en un destino con acceso restringido.

## Higiene del repositorio

No versionar:

- `.env` y credenciales.
- Documentos de usuarios.
- Base de datos local.
- Sesiones, logs y cachés.
- `vendor/`, `node_modules/`, builds temporales.

Antes de limpiar archivos ya versionados, hacer inventario, copia segura y acordar si también se purgará el historial remoto.
