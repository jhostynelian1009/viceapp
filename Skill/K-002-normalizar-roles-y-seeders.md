---
id: K-002
nombre: Normalizar roles, usuarios de entorno y seeders
fase: 2
estado: COMPLETADA
---

## Objetivo

Eliminar duplicidad de roles y lograr una inicialización idempotente y coherente.

## Entradas

- `Spec/S02-diagnostico-errores.md`: `ERR-007`, `ERR-016`, `ERR-017`, `ERR-022`
- `Spec/S05-roles-autorizacion.md`
- `Spec/S07-modelo-datos.md`

## Salidas

- Identificadores canónicos de roles (`docente`, `secretaria`, `vicerrectorado`).
- Migración o comando seguro para consolidar roles existentes.
- Seeders idempotentes con materias incluidas.
- Estrategia explícita para usuarios demo solo en local/testing.
- Pruebas de ejecución repetida.

## Restricciones

- No perder asociaciones existentes en `model_has_roles`.
- No crear credenciales demo en producción.
- No asignar permisos académicos al administrador técnico por conveniencia.
- **ADVERTENCIA CRÍTICA**: Nunca ejecutar `migrate:fresh`, `db:wipe` ni ningún comando destructivo sobre la base de datos MySQL `viceapp` ni entornos persistentes.

## Procedimiento

1. Medir roles y asignaciones actuales.
2. Definir mapping de nombres antiguos a canónicos (`docente`, `secretaria`, `vicerrectorado`).
3. Consolidar asignaciones dentro de una transacción.
4. Hacer seeders repetibles y llamar `SubjectSeeder`.
5. Marcar cuentas de prueba verificadas solo cuando aplique al entorno.
6. Bloquear seeders demo fuera de local/testing.
7. Reglas de verificación segura e idempotencia:
   - `migrate:fresh --seed` solo puede utilizarse en una base efímera y aislada destinada a pruebas.
   - Debe comprobarse previamente que `APP_ENV=testing`.
   - Debe comprobarse que la conexión sea SQLite.
   - Debe comprobarse que `DB_DATABASE=:memory:` o que sea un archivo temporal creado exclusivamente para la prueba.
   - Está estrictamente prohibido ejecutarlo sobre MySQL/MariaDB local, compartido, institucional o de producción (NUNCA sobre la base MySQL `viceapp`).
   - La idempotencia debe comprobarse preferentemente mediante pruebas automatizadas con SQLite en memoria.
   - Si no puede demostrarse el aislamiento, la verificación debe detenerse de inmediato.

## Criterios de aceptación

- Dada una BD con variantes de rol, cuando se migra, entonces cada usuario conserva un único rol canónico equivalente.
- Dada una BD vacía, cuando se ejecuta `migrate --seed` en entorno aislado, entonces existen roles, materias y cuentas locales esperadas.
- Cuando los seeders se ejecutan dos veces, entonces no fallan ni duplican registros.
- No se avanzó a K-003.
