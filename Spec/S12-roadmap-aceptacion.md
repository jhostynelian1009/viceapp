# S12 — Roadmap y aceptación global

## Orden obligatorio

| Fase | Skills | Resultado |
|---|---|---|
| 1. Línea base | `K-001` | Entorno reproducible e inventario validado |
| 2. Identidad y seguridad | `K-002`–`K-004` | Roles, permisos y documentos privados |
| 3. Núcleo académico | `K-005`–`K-006` | Flujo versionado y estructura académica |
| 4. Experiencia operativa | `K-007`–`K-010` | Notificaciones, reportes, preview y UI |
| 5. Calidad y entrega | `K-011`–`K-012` | Suite, hardening, despliegue y manuales |

No adelantar funciones visuales si las policies o el almacenamiento privado siguen pendientes.

## Mejoras priorizadas

### P0 — Bloqueantes

- Resolver `ERR-001` a `ERR-010`.
- Proteger documentos y datos.
- Evitar pérdida histórica.
- Normalizar roles y cuentas.

### P1 — Núcleo funcional

- Resolver `ERR-011` a `ERR-020`.
- Implementar estructura académica, versiones, revisiones y auditoría.
- Corregir notificaciones y el resumen de aprobadas, pendientes y rechazadas.

### P2 — Calidad y experiencia

- Resolver `ERR-021` a `ERR-030`.
- Mejorar dashboard, previsualización local, accesibilidad, documentación y entrega.

## Criterios de aceptación global

- `CA-001`: Ningún rol accede por URL directa a funciones no autorizadas.
- `CA-002`: Ningún documento institucional queda públicamente accesible.
- `CA-003`: Desactivar usuarios o catálogos no elimina planificaciones históricas.
- `CA-004`: Cada aprobación/rechazo identifica actor, fecha, versión y observación.
- `CA-005`: Una reentrega conserva la versión anterior.
- `CA-006`: Los reportes coinciden con filtros y estados reales.
- `CA-011`: El resumen oficial contiene aprobadas, pendientes y rechazadas; no mezcla borradores con pendientes.
- `CA-007`: Seeders, instalación y build son reproducibles desde un clon limpio.
- `CA-008`: La suite cubre autorizaciones y flujo principal sin fallas.
- `CA-009`: Existe respaldo probado antes del despliegue institucional.
- `CA-010`: Manual técnico y manual de usuario reflejan el comportamiento entregado.

## Gate institucional resuelto

`DEC-001` a `DEC-004` fueron confirmadas por el docente. `DEC-005` queda postergada y no bloquea el MVP. K-005, K-006 y K-008 pueden ejecutarse con las reglas ya documentadas.
