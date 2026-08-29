---
id: K-005
nombre: Implementar estructura académica
fase: 3
estado: PENDIENTE
---

## Objetivo

Clasificar planificaciones por la estructura académica real de la institución.

## Entradas

- `DEC-002` y `DEC-003` confirmadas en `Spec/S01-vision-alcance.md`
- `Spec/S01-vision-alcance.md`
- `Spec/S03-requisitos-funcionales.md`: `RF-010`–`RF-015`
- `Spec/S07-modelo-datos.md`

## Salidas

- Migraciones y modelos de área académica, curso, paralelo y asignatura.
- Asignaciones docente-asignatura-curso-paralelo sin duplicados.
- CRUD autorizado de catálogos con activación/desactivación.
- Formularios y filtros que usan únicamente opciones válidas.
- Seeders académicos ficticios para local/testing.

## Restricciones

- No añadir años lectivos, quimestres, trimestres u otros catálogos no solicitados en el MVP.
- No borrar `subjects` o planificaciones existentes.
- Migrar datos históricos a una categoría explícita cuando falte información.

## Procedimiento

1. Verificar las decisiones institucionales registradas.
2. Crear migraciones aditivas y constraints.
3. Implementar modelos, relaciones y policies.
4. Migrar planificaciones existentes sin pérdida.
5. Crear CRUD y asignaciones docentes.
6. Añadir pruebas de unicidad, inactivación y acceso.

## Criterios de aceptación

- Dada una asignación, cuando se repite la misma combinación, entonces la BD la rechaza.
- Dado un catálogo usado, cuando se desactiva, entonces sigue visible en historia pero no en nuevas selecciones.
- Dado un docente, cuando crea borrador, entonces solo elige asignaciones activas propias.
- No se avanzó a K-006.
