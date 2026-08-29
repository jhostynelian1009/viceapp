# S03 — Requisitos funcionales

## Autenticación y usuarios

- `RF-001`: El sistema permitirá iniciar y cerrar sesión.
- `RF-002`: Solo personal autorizado podrá crear cuentas institucionales.
- `RF-003`: Secretaría o el rol autorizado podrá crear, editar, activar y desactivar docentes.
- `RF-004`: Ningún usuario podrá asignarse a sí mismo un rol superior.
- `RF-005`: El sistema impedirá eliminar al último usuario con autoridad administrativa.
- `RF-006`: Las cuentas inactivas no podrán autenticarse.

## Estructura académica

- `RF-010`: El personal administrativo autorizado podrá administrar áreas académicas.
- `RF-011`: El personal administrativo autorizado podrá administrar cursos.
- `RF-012`: El personal administrativo autorizado podrá administrar paralelos.
- `RF-013`: El personal administrativo autorizado podrá administrar asignaturas y relacionarlas con su área académica.
- `RF-014`: Se podrán registrar asignaciones de docente, asignatura, curso y paralelo.
- `RF-015`: Solo podrán seleccionarse docentes, áreas, cursos, paralelos y asignaturas activas.

## Planificaciones

- `RF-020`: El docente podrá crear una planificación semanal en borrador dentro de una asignación válida.
- `RF-021`: Podrá cargar un archivo PDF, DOC o DOCX bajo límites configurables.
- `RF-022`: Los archivos se almacenarán de forma privada.
- `RF-023`: El docente podrá editar metadatos y reemplazar el archivo mientras esté en borrador o rechazado.
- `RF-024`: Cada reemplazo conservará una versión y no sobrescribirá la evidencia anterior.
- `RF-025`: Solo el propietario podrá enviar su planificación a revisión.
- `RF-026`: Antes del envío se validarán semana o rango semanal, área académica, docente, curso, paralelo, asignatura y archivo.
- `RF-027`: El docente podrá consultar estados, comentarios e historial de sus planificaciones.
- `RF-028`: Vicerrectorado podrá consultar todas las planificaciones enviadas para revisión.
- `RF-029`: Solo Vicerrectorado podrá aprobar o rechazar mediante una transición válida.
- `RF-030`: Rechazar exigirá un motivo u observación.
- `RF-031`: Se registrará revisor, fecha, estado anterior, estado nuevo y observación.
- `RF-032`: Una planificación aprobada no podrá alterarse; una corrección generará una nueva versión o reapertura auditada.
- `RF-033`: Los documentos históricos no se eliminarán físicamente desde la interfaz ordinaria.

## Colaboración y notificaciones

- `RF-040`: Los usuarios autorizados podrán comentar una planificación visible para ellos.
- `RF-041`: El autor podrá eliminar su comentario solo si no forma parte de una decisión formal; la auditoría se conserva.
- `RF-042`: El docente recibirá notificación por comentario, rechazo y aprobación.
- `RF-043`: Vicerrectorado recibirá notificación por nueva entrega o reentrega.
- `RF-044`: Cada usuario solo podrá leer o marcar sus propias notificaciones.

## Consultas y reportes

- `RF-050`: Los listados tendrán búsqueda, filtros y paginación.
- `RF-051`: Los filtros mínimos serán área académica, docente, curso, paralelo, asignatura, semana y estado.
- `RF-052`: El dashboard de Vicerrectorado mostrará totales de aprobadas, pendientes de revisión y rechazadas.
- `RF-053`: Los borradores no se contabilizarán como pendientes oficiales.
- `RF-054`: El detalle del reporte reflejará exactamente los filtros aplicados.
- `RF-055`: Si se exporta el resumen, mostrará fecha/hora de generación y usuario generador; el formato de exportación no es requisito obligatorio del MVP.

## Auditoría y operación

- `RF-060`: Las acciones críticas se registrarán en una bitácora.
- `RF-061`: Se podrán consultar eventos de una planificación sin modificar la bitácora.
- `RF-062`: El sistema proporcionará comandos seguros de creación de administrador y preparación del entorno.
