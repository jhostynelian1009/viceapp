# Índice de especificaciones

`Spec/` es la fuente de verdad del producto. Describe **qué** debe cumplir el sistema y por qué. Los procedimientos de implementación están en `Skill/`.

| Spec | Contenido |
|---|---|
| `S01` | Visión, problema, objetivos y alcance |
| `S02` | Diagnóstico técnico y catálogo de errores |
| `S03` | Requisitos funcionales |
| `S04` | Requisitos no funcionales |
| `S05` | Roles, permisos y gobierno de cuentas |
| `S06` | Flujo de planificaciones, versiones y auditoría |
| `S07` | Modelo de datos objetivo |
| `S08` | Arquitectura y convenciones técnicas |
| `S09` | Seguridad, privacidad y conservación documental |
| `S10` | Estrategia de pruebas y calidad |
| `S11` | Despliegue, operación, respaldos y recuperación |
| `S12` | Roadmap, prioridades y aceptación global |

## Estados documentales

- `APROBADO_TECNICO`: decisión técnica respaldada por el diagnóstico.
- `PROPUESTO`: mejora recomendada, todavía no implementada.
- `PENDIENTE_INSTITUCION`: requiere respuesta de la institución.
- `IMPLEMENTADO`: solo se usa después de comprobarlo con código y pruebas.

## Trazabilidad

Cada requisito tiene un identificador estable:

- `ERR-xxx`: error existente.
- `RF-xxx`: requisito funcional.
- `RNF-xxx`: requisito no funcional.
- `SEC-xxx`: control de seguridad.
- `DAT-xxx`: regla de datos.
- `CA-xxx`: criterio de aceptación global.

Las skills deben indicar explícitamente los identificadores que resuelven.
