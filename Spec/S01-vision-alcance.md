# S01 — Visión y alcance

## Visión

Construir un sistema web institucional que permita controlar el ciclo de vida de las planificaciones docentes de la U.E. Fiscomisional 10 de Agosto con acceso por roles, trazabilidad de revisiones, conservación de versiones y reportes confiables.

## Problema

El prototipo actual permite cargar y aprobar archivos, pero no garantiza autorización completa, conservación histórica, estructura académica, seguimiento de versiones ni reportes correctos. Estas carencias impiden considerarlo apto para información institucional real.

## Objetivo general

Digitalizar y asegurar la recepción, revisión, corrección, aprobación, consulta y archivo de planificaciones docentes, disminuyendo pérdida de documentos, duplicidad, demoras y falta de evidencia sobre las decisiones.

## Objetivos específicos

1. Corregir vulnerabilidades y errores funcionales del prototipo.
2. Modelar la estructura académica necesaria para clasificar las planificaciones.
3. Implementar un flujo trazable con versiones, observaciones y responsables.
4. Proporcionar consultas, alertas y reportes confiables por rol.
5. Asegurar respaldos, pruebas y documentación de operación.

## Actores

- **Docente:** prepara, carga, corrige y consulta sus planificaciones.
- **Secretaría:** administra cuentas, catálogos y organización del sistema; no aprueba ni rechaza.
- **Vicerrectorado:** revisa, aprueba, rechaza y consulta indicadores; es la única autoridad académica de decisión.
- **Administrador técnico:** configura el sistema y opera respaldos; no reemplaza decisiones académicas.

## Dentro del alcance

- Autenticación y administración controlada de cuentas.
- Roles y autorización granular.
- Estructura académica con área académica, docente, curso, paralelo y asignatura.
- Planificaciones semanales con carga privada de PDF, DOC y DOCX.
- Versionado de documentos.
- Flujo de envío, revisión, corrección, aprobación y archivo.
- Comentarios y motivos de rechazo.
- Notificaciones internas.
- Búsqueda, filtros, dashboard y reportes.
- Auditoría, pruebas, respaldos y despliegue documentado.

## Fuera del alcance inicial

- Matrículas, asistencia y calificaciones estudiantiles.
- LMS, clases virtuales o entrega de tareas estudiantiles.
- Firma electrónica certificada.
- Aplicación móvil nativa.
- Reescritura en otro framework.
- Integración con Google Drive durante el MVP.

## Decisiones institucionales registradas

Fuente: indicaciones del docente de prácticas comunicadas el 25 de agosto de 2026.

| ID | Decisión | Estado |
|---|---|---|
| `DEC-001` | Secretaría solo administra. Vicerrectorado es la única autoridad que revisa, aprueba o rechaza. | `CONFIRMADO_DOCENTE` |
| `DEC-002` | Se gestionan planificaciones semanales en Word o PDF. | `CONFIRMADO_DOCENTE` |
| `DEC-003` | Cada planificación se organiza por área académica, docente, curso, paralelo y asignatura. | `CONFIRMADO_DOCENTE` |
| `DEC-004` | El reporte requerido resume aprobadas, pendientes y rechazadas. | `CONFIRMADO_DOCENTE` |
| `DEC-005` | Google Drive no fue solicitado; se posterga fuera del MVP y se prioriza carga directa. | `POSTERGADO_MVP` |

En los reportes, `pendiente` significa una planificación enviada y todavía no resuelta por Vicerrectorado. Los borradores del docente no se contabilizan como pendientes oficiales.
