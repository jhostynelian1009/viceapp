# Reglas del proyecto para agentes

Este repositorio corresponde al **Sistema de Gestión de Planificaciones Docentes de la U.E. Fiscomisional 10 de Agosto**.

## Fuente de verdad

Antes de modificar código, el agente debe leer, en este orden:

1. `PromptMaster.md`.
2. `Spec/README.md`.
3. Las especificaciones indicadas por la skill activa.
4. `Skill/README.md`.
5. El runbook K-xxx solicitado, usando el nombre exacto indicado en `Skill/README.md`.

Las decisiones de `Spec/` prevalecen sobre comentarios antiguos, `blueprint.md` y comportamiento accidental del código actual. Si dos especificaciones se contradicen, detenerse y reportar el conflicto.

## Forma de ejecución

- Ejecutar **una sola skill K-xxx por turno**. No avanzar automáticamente a la siguiente.
- Antes de editar, inspeccionar el estado Git, código afectado, migraciones y pruebas existentes.
- Preservar cambios previos del usuario y no modificar archivos ajenos al alcance.
- No hacer `commit`, `push`, `merge`, rebase ni abrir PR sin solicitud expresa.
- No eliminar datos, documentos ni migraciones existentes sin respaldo y autorización expresa.
- Preferir cambios pequeños, reversibles y cubiertos por pruebas.
- Registrar el resultado en `Spec/EXECUTION_LOG.md` usando su plantilla.

## Restricciones técnicas

- Mantener Laravel 12, PHP 8.2+, Blade, Tailwind CSS, Alpine.js y MySQL/MariaDB.
- No reescribir el sistema en otro framework.
- La interfaz y mensajes para usuarios se mantienen en español.
- El alcance funcional confirmado son planificaciones semanales en PDF o Word (`.pdf`, `.doc`, `.docx`).
- Secretaría administra cuentas y catálogos, pero no aprueba ni rechaza planificaciones.
- Vicerrectorado es la única autoridad de revisión, aprobación y rechazo.
- Cada planificación se clasifica por área académica, docente, curso, paralelo y asignatura.
- El reporte mínimo muestra el resumen de aprobadas, pendientes de revisión y rechazadas.
- Los identificadores internos, enums y nombres de clases deben ser consistentes y sin tildes.
- Toda autorización se valida en el servidor mediante middleware, policies o gates. Ocultar botones no constituye autorización.
- Los documentos institucionales deben almacenarse de forma privada.
- Nunca versionar `.env`, credenciales, sesiones, logs, `vendor/`, `node_modules/` ni documentos reales cargados por usuarios.
- No destruir planificaciones históricas al eliminar o desactivar usuarios, materias o estructura académica.
- Las decisiones marcadas `PENDIENTE_INSTITUCION` no se implementan como reglas definitivas.
- Google Drive queda postergado fuera del MVP; no invertir trabajo en repararlo salvo nueva solicitud.

## Verificación mínima

Cuando el entorno lo permita, cada cambio debe ejecutar:

```bash
php artisan optimize:clear
php artisan test
vendor/bin/pint --test
npm run build
```

Las skills pueden exigir verificaciones adicionales. Si una herramienta no está disponible, reportarlo como bloqueo; no declarar la tarea completa.

## Entrega de cada skill

El reporte final debe incluir:

- Skill ejecutada.
- Especificaciones consultadas.
- Archivos modificados.
- Migraciones o comandos ejecutados.
- Pruebas realizadas y resultado.
- Riesgos, decisiones pendientes y bloqueos.
- Confirmación de que no se avanzó a la siguiente skill.
