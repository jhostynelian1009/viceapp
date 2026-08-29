# Índice de skills de implementación

`Skill/` contiene runbooks ejecutables. Cada archivo define **cómo** implementar una parte de las especificaciones. Antigravity dispone además de adaptadores nativos en `.agents/skills/`.

## Regla principal

Ejecutar una sola skill por turno y detenerse después de reportar sus resultados. Una skill `PARCIAL` o `BLOQUEADA` no habilita la siguiente.

| Orden | Skill | Dependencia | Estado inicial |
|---:|---|---|---|
| 1 | `K-001-preflight-y-linea-base.md` | Ninguna | PENDIENTE |
| 2 | `K-002-normalizar-roles-y-seeders.md` | K-001 | PENDIENTE |
| 3 | `K-003-cerrar-autorizacion-y-cuentas.md` | K-002 | PENDIENTE |
| 4 | `K-004-privatizar-documentos.md` | K-003 | PENDIENTE |
| 5 | `K-005-estructura-academica.md` | K-004 | PENDIENTE |
| 6 | `K-006-flujo-versiones-y-auditoria.md` | K-005 | PENDIENTE |
| 7 | `K-007-comentarios-y-notificaciones.md` | K-006 | PENDIENTE |
| 8 | `K-008-reportes-y-dashboard.md` | K-006 | PENDIENTE |
| 9 | `K-009-drive-y-previsualizacion.md` | K-004; Drive postergado | PENDIENTE |
| 10 | `K-010-ui-y-accesibilidad.md` | K-007–K-009 | PENDIENTE |
| 11 | `K-011-pruebas-y-hardening.md` | K-001–K-010 | PENDIENTE |
| 12 | `K-012-despliegue-y-entrega.md` | K-011 | PENDIENTE |

## Contrato común

Antes de editar:

- Verificar rama y `git status`.
- Leer entradas y Specs ancladas.
- Confirmar que la skill anterior terminó.
- Identificar datos o archivos que podrían perderse.

Después de editar:

- Ejecutar criterios de aceptación.
- Añadir pruebas proporcionales al riesgo.
- Registrar la ejecución.
- Reportar sin avanzar a la siguiente skill.
