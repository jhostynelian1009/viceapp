# S05 — Roles, permisos y gobierno de cuentas

## Identificadores canónicos

Los roles internos serán minúsculos y sin tildes:

- `teacher`
- `secretary`
- `vice_principal`
- `technical_admin` solo si se confirma la necesidad operativa.

La interfaz mostrará Docente, Secretaría, Vicerrectorado y Administrador técnico. No mezclar etiquetas visibles con identificadores internos.

## Matriz base

| Acción | Docente | Secretaría | Vicerrectorado | Admin técnico |
|---|:---:|:---:|:---:|:---:|
| Ver dashboard propio | Sí | Sí | Sí | Sí |
| Crear/editar borrador propio | Sí | No | No | No |
| Enviar planificación propia | Sí | No | No | No |
| Ver metadatos administrativos | No | Sí | Sí | Solo soporte autorizado |
| Ver archivo de planificación ajena | No | No por defecto | Sí | Solo soporte autorizado |
| Comentar revisión | Propia | No | Sí | No |
| Aprobar/rechazar | No | No | Sí | No |
| Gestionar docentes | No | Sí | Sí | Soporte técnico |
| Gestionar áreas, cursos, paralelos y asignaturas | No | Sí | Sí | Soporte técnico |
| Ver resumen global | No | Sí, sin decidir | Sí | No por defecto |
| Gestionar configuración técnica | No | No | No | Sí |

## Reglas

1. Las rutas se protegen con middleware y cada recurso con policy.
2. La policy valida rol, propiedad, ámbito académico y estado del recurso.
3. El route model binding nunca es autorización suficiente.
4. Una cuenta sin rol no obtiene capacidades del negocio.
5. La administración de usuarios usa activación/desactivación; no borrado físico ordinario.
6. No se permitirá autoelevar permisos ni eliminar/desactivar al último responsable administrativo.
7. Secretaría no revisa, aprueba ni rechaza contenido académico.
8. Vicerrectorado es la única autoridad que aprueba o rechaza.
9. El administrador técnico no aprueba contenido académico.

## Permisos sugeridos

- `users.view`, `users.create`, `users.update`, `users.deactivate`
- `academic_structure.manage`
- `plannings.create`, `plannings.view_own`, `plannings.submit`
- `plannings.review`, `plannings.approve`, `plannings.reject` solo para Vicerrectorado
- `reports.view`, `reports.export`
- `audit.view`

La implementación puede usar roles con permissions de Spatie, pero las decisiones sobre recursos concretos deben permanecer en policies.
