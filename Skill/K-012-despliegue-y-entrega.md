---
id: K-012
nombre: Preparar despliegue y entrega institucional
fase: 5
estado: PENDIENTE
---

## Objetivo

Entregar una versión instalable, respaldable, recuperable y documentada.

## Entradas

- `Spec/S11-despliegue-operacion.md`
- `Spec/S12-roadmap-aceptacion.md`
- Evidencia de K-011

## Salidas

- README de instalación actualizado.
- Checklist de producción y rollback.
- Procedimiento de backup y restauración probado.
- Manual técnico y manual de usuario por rol.
- Release candidate con criterios globales evaluados.

## Restricciones

- No desplegar ni usar credenciales reales sin autorización expresa.
- No ejecutar migraciones en producción sin respaldo verificado.
- No declarar listo si existen P0 abiertos o pruebas fallidas.

## Procedimiento

1. Validar `CA-001` a `CA-010`.
2. Preparar configuración y comandos de instalación reproducibles.
3. Documentar despliegue, rollback, cron, colas y permisos.
4. Ejecutar backup y restauración en entorno controlado.
5. Realizar smoke tests por rol en staging.
6. Generar manuales y lista de riesgos residuales.
7. Presentar gate de aprobación; no desplegar automáticamente.

## Criterios de aceptación

- Dado un entorno limpio, cuando se sigue el README, entonces la aplicación instala y compila.
- Dado un respaldo, cuando se restaura en staging, entonces BD y documentos son consistentes.
- Dado cada rol, cuando ejecuta el smoke test, entonces solo ve y realiza acciones permitidas.
- La entrega identifica claramente pendientes y no realiza despliegue sin aprobación.
