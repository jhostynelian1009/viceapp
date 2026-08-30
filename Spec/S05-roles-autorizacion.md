# S05 — Roles, permisos y gobierno de cuentas

## Identificadores canónicos

Los roles internos del sistema se definen en minúsculas y sin tildes:

- `docente`
- `secretaria`
- `vicerrectorado`

La existencia de un rol de administrador técnico (`technical_admin`) queda catalogada como `PENDIENTE_INSTITUCION`. No recibe permisos académicos ni se implementa en el MVP sin confirmación institucional expresa.

Las etiquetas visibles en interfaz continúan siendo:
- Docente
- Secretaría
- Vicerrectorado

No mezclar las etiquetas visibles en UI con los identificadores internos canónicos.

## Matriz base

| Acción | Docente (`docente`) | Secretaría (`secretaria`) | Vicerrectorado (`vicerrectorado`) | Admin técnico (`PENDIENTE_INSTITUCION`) |
|---|:---:|:---:|:---:|:---:|
| Ver dashboard propio | Sí | Sí | Sí | Pendiente |
| Crear/editar borrador propio | Sí | No | No | No |
| Enviar planificación propia | Sí | No | No | No |
| Ver metadatos administrativos | No | Sí | Sí | Pendiente |
| Ver archivo de planificación ajena | No | No por defecto | Sí | Pendiente |
| Comentar revisión | Propia | No | Sí | No |
| Aprobar/rechazar | No | No | Sí | No |
| Gestionar docentes y catálogos | No | Sí | Sí | Pendiente |
| Gestionar estructura académica | No | Sí | Sí | Pendiente |
| Ver resumen global | No | Sí, sin decidir | Sí | Pendiente |
| Configuración técnica | No | No | No | Pendiente |

## Reglas

1. Las rutas se protegen con middleware y cada recurso con policy.
2. La policy valida rol, propiedad, ámbito académico y estado del recurso.
3. El route model binding nunca es autorización suficiente.
4. Una cuenta sin rol no obtiene capacidades del negocio.
5. La administración de usuarios usa activación/desactivación; no borrado físico ordinario.
6. No se permitirá autoelevar permisos ni eliminar/desactivar al último responsable administrativo.
7. **Secretaría**: Administra cuentas, docentes y catálogos académicos. Puede consultar el resumen institucional pero NO aprueba, rechaza ni revisa contenido académico de planificaciones.
8. **Vicerrectorado**: Revisa, comenta, aprueba y rechaza planificaciones. Es la ÚNICA autoridad de aprobación y rechazo.
9. El administrador técnico no aprueba ni revisa contenido académico.

## Permisos sugeridos

- `users.view`, `users.create`, `users.update`, `users.deactivate`
- `academic_structure.manage`
- `plannings.create`, `plannings.view_own`, `plannings.submit`
- `plannings.review`, `plannings.approve`, `plannings.reject` solo para Vicerrectorado (`vicerrectorado`)
- `reports.view`, `reports.export`
- `audit.view`

La implementación utiliza roles con permissions de Spatie, pero las decisiones sobre recursos concretos deben permanecer en policies.
