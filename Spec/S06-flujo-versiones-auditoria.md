# S06 — Flujo, versiones y auditoría

## Estados propuestos

Los códigos internos no llevan tildes:

- `draft`: borrador editable.
- `pending`: enviado y pendiente de decisión de Vicerrectorado.
- `rejected`: requiere corrección.
- `approved`: aprobado e inmutable.

## Transiciones permitidas

| Desde | Hacia | Actor | Condición |
|---|---|---|---|
| `draft` | `pending` | Docente propietario | Metadatos semanales y archivo válidos |
| `rejected` | `pending` | Docente propietario | Nueva versión creada |
| `pending` | `rejected` | Vicerrectorado | Motivo obligatorio |
| `pending` | `approved` | Vicerrectorado | Revisión válida |

Ninguna transición se realiza aceptando ciegamente un valor `status` enviado por el navegador.

## Versiones

- La entidad planificación representa el expediente lógico.
- Cada archivo cargado genera una `planning_version` inmutable.
- La planificación apunta a su versión actual.
- Una reentrega desde `rejected` requiere versión nueva.
- Las versiones anteriores permanecen descargables solo para usuarios autorizados.
- Secretaría no participa en transiciones de estado.

## Revisiones

Cada revisión registra:

- Planificación y versión revisada.
- Revisor.
- Estado anterior y nuevo.
- Decisión.
- Observación o motivo.
- Fecha y hora.
- Datos técnicos mínimos de auditoría, evitando información sensible innecesaria.

## Auditoría

Eventos mínimos:

- Creación, edición y envío.
- Carga/reemplazo de archivo.
- Comentario relevante.
- Aprobación, rechazo, reapertura y archivo.
- Creación, cambio de rol, activación o desactivación de cuenta.
- Cambios de estructura académica.
- Exportación de reportes sensibles.

La auditoría es append-only desde la aplicación. No se ofrece una acción ordinaria para editarla o eliminarla.
