---
id: K-006
nombre: Implementar flujo, versiones y auditoría
fase: 3
estado: PENDIENTE
---

## Objetivo

Reemplazar el cambio libre de estado por un flujo institucional trazable y versionado.

## Entradas

- `DEC-001` confirmada en `Spec/S01-vision-alcance.md`
- `Spec/S03-requisitos-funcionales.md`: `RF-020`–`RF-033`, `RF-060`–`RF-061`
- `Spec/S06-flujo-versiones-auditoria.md`
- `Spec/S07-modelo-datos.md`

## Salidas

- Enum de estados y servicio de transiciones.
- Tabla y servicio de versiones.
- Tabla de revisiones y bitácora.
- Edición/reentrega controlada.
- Motivo obligatorio de rechazo.
- Migración de planificaciones actuales a versión 1.

## Restricciones

- No crear revisión de Secretaría: solo Vicerrectorado cambia `pending` a `approved` o `rejected`.
- No sobrescribir archivos aprobados o versiones anteriores.
- No aceptar el estado final directamente desde el request.

## Procedimiento

1. Verificar `DEC-001` y la definición de pendiente ya registradas.
2. Crear enums, tablas y servicios dentro de transacciones.
3. Migrar estados y generar versión inicial.
4. Sustituir `updateStatus` por comandos de dominio explícitos.
5. Implementar edición de borrador/rechazado y reentrega versionada.
6. Registrar revisiones y auditoría.
7. Probar todas las transiciones válidas e inválidas.

## Criterios de aceptación

- Dado un rechazo, cuando no tiene motivo, entonces no se registra.
- Dada una reentrega, cuando se carga corrección, entonces aumenta la versión y conserva la anterior.
- Dada una aprobación, cuando finaliza, entonces registra revisor, versión y fecha.
- Dada una transición inválida, cuando se intenta, entonces no cambia ningún registro ni archivo.
- No se avanzó a K-007.
