# S07 — Modelo de datos objetivo

## Entidades

| Entidad | Propósito | Campos mínimos |
|---|---|---|
| `users` | Cuentas institucionales | nombre, email, password, active, timestamps |
| `academic_areas` | Áreas académicas | name, code, active |
| `courses` | Cursos | name, active |
| `parallels` | Paralelos | name, active |
| `subjects` | Asignaturas | academic_area_id, name, code, active |
| `teaching_assignments` | Carga docente | teacher_id, subject_id, course_id, parallel_id, active |
| `plannings` | Expediente semanal | assignment_id, title, week_start, week_end, status, current_version_id, submitted_at |
| `planning_versions` | Archivos inmutables | planning_id, version, file_path, original_name, mime, size, checksum, uploaded_by |
| `planning_reviews` | Decisiones | planning_id, version_id, reviewer_id, from_status, to_status, decision, comment |
| `comments` | Conversación | planning_id, user_id, body, edited_at/deleted_at según regla |
| `audit_logs` | Bitácora | actor_id, event, auditable_type/id, old_values, new_values, created_at |

## Reglas de integridad

- `DAT-001`: Cada asignatura pertenece a un área académica y su código será único cuando se utilice.
- `DAT-002`: Una asignación no podrá duplicar la combinación docente, asignatura, curso y paralelo.
- `DAT-003`: Una versión será única por `(planning_id, version)`.
- `DAT-004`: El archivo actual debe pertenecer a la misma planificación.
- `DAT-005`: Catálogos usados por registros históricos no se eliminan en cascada.
- `DAT-006`: Usuarios con historial se desactivan o usan soft delete.
- `DAT-007`: El checksum permitirá detectar duplicados o alteraciones accidentales.
- `DAT-008`: Estados y decisiones usarán enums o constraints coherentes.
- `DAT-009`: Área, docente, curso, paralelo, asignatura, semana y estado tendrán índices adecuados.
- `DAT-010`: Migraciones desde estados españoles existentes conservarán todos los registros.

## Estrategia de migración

1. Respaldar BD y documentos.
2. Crear nuevas tablas y columnas de forma aditiva.
3. Mapear roles y estados existentes.
4. Crear versión 1 para cada planificación histórica.
5. Validar conteos, propietarios, archivos y checksums.
6. Activar nuevas restricciones después de migrar.
7. Retirar columnas antiguas solo en una migración posterior y con autorización.

No combinar creación, migración de datos y eliminación irreversible en una sola migración.
