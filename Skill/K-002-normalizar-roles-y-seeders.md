---
id: K-002
nombre: Normalizar roles, usuarios de entorno y seeders
fase: 2
estado: PENDIENTE
---

## Objetivo

Eliminar duplicidad de roles y lograr una inicialización idempotente y coherente.

## Entradas

- `Spec/S02-diagnostico-errores.md`: `ERR-007`, `ERR-016`, `ERR-017`, `ERR-022`
- `Spec/S05-roles-autorizacion.md`
- `Spec/S07-modelo-datos.md`

## Salidas

- Identificadores canónicos de roles.
- Migración o comando seguro para consolidar roles existentes.
- Seeders idempotentes con materias incluidas.
- Estrategia explícita para usuarios demo solo en local/testing.
- Pruebas de ejecución repetida.

## Restricciones

- No perder asociaciones existentes en `model_has_roles`.
- No crear credenciales demo en producción.
- No asignar permisos académicos al administrador técnico por conveniencia.

## Procedimiento

1. Medir roles y asignaciones actuales.
2. Definir mapping de nombres antiguos a canónicos.
3. Consolidar asignaciones dentro de una transacción.
4. Hacer seeders repetibles y llamar `SubjectSeeder`.
5. Marcar cuentas de prueba verificadas solo cuando aplique al entorno.
6. Bloquear seeders demo fuera de local/testing.
7. Probar `migrate:fresh --seed` y doble ejecución de seeders.

## Criterios de aceptación

- Dada una BD con variantes de rol, cuando se migra, entonces cada usuario conserva un único rol canónico equivalente.
- Dada una BD vacía, cuando se ejecuta `migrate --seed`, entonces existen roles, materias y cuentas locales esperadas.
- Cuando los seeders se ejecutan dos veces, entonces no fallan ni duplican registros.
- No se avanzó a K-003.
