---
id: K-007
nombre: Corregir comentarios y notificaciones
fase: 4
estado: PENDIENTE
---

## Objetivo

Proporcionar colaboración autorizada y notificaciones coherentes con el flujo.

## Entradas

- `Spec/S02-diagnostico-errores.md`: `ERR-004`, `ERR-005`, `ERR-011`, `ERR-028`
- `Spec/S03-requisitos-funcionales.md`: `RF-040`–`RF-044`
- `Spec/S05-roles-autorizacion.md`

## Salidas

- Contrato único de payload de notificación.
- Campana funcional con enlaces y marcado seguro.
- Notificaciones a Vicerrectorado por envío/reentrega y al docente por comentario, rechazo o aprobación.
- Validación y policy de comentarios.
- Pruebas de destinatarios y propiedad.

## Restricciones

- No notificar a usuarios fuera del ámbito.
- No confiar en IDs de notificación sin relacionarlos con el usuario.
- No permitir HTML no confiable en comentarios o notificaciones.

## Procedimiento

1. Definir tipos y payload común.
2. Corregir componente de notificaciones.
3. Resolver notificación desde `auth()->user()->notifications()`.
4. Autorizar comentarios y limitar longitud.
5. Conectar eventos del workflow con destinatarios correctos.
6. Probar contenido, enlace, lectura y aislamiento entre usuarios.

## Criterios de aceptación

- Dada una entrega, cuando se envía, entonces recibe notificación Vicerrectorado y no Secretaría.
- Dado un rechazo, cuando se registra, entonces el propietario recibe motivo y enlace.
- Dada una notificación ajena, cuando se manipula su ID, entonces no cambia.
- La campana renderiza sin claves inexistentes.
- No se avanzó a K-008.
