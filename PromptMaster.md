# Prompt Maestro — Modernización del Gestor de Planificaciones Docentes

## Objetivo

Evolucionar el prototipo existente a un sistema institucional seguro, trazable y mantenible para gestionar la creación, entrega, revisión, corrección, aprobación y archivo de planificaciones docentes de la U.E. Fiscomisional 10 de Agosto.

## Contexto obligatorio

El sistema existente es un monolito Laravel 12 con PHP 8.2+, Blade, Tailwind CSS, Alpine.js, MySQL/MariaDB, Spatie Permission, Dompdf, PHPWord y Google API Client.

No se parte desde cero. Se conserva la arquitectura Laravel y se corrige progresivamente el código actual.

## Reglas institucionales confirmadas

- El sistema gestiona planificaciones **semanales** en Word o PDF.
- Secretaría administra el sistema, pero no toma decisiones académicas.
- Vicerrectorado revisa y es la única autoridad que aprueba o rechaza.
- La clasificación obligatoria es: área académica, docente, curso, paralelo y asignatura.
- El reporte requerido resume planificaciones aprobadas, pendientes y rechazadas.
- Google Drive no forma parte del MVP confirmado; la carga directa es el flujo principal.

## Protocolo para Antigravity

1. Leer `AGENTS.md`, este archivo, `Spec/README.md` y `Skill/README.md`.
2. Identificar la skill K-xxx solicitada.
3. Leer su runbook y únicamente las Specs que este referencia.
4. Presentar un preflight breve: rama, estado Git, alcance, riesgos y pruebas previstas.
5. Implementar solo esa skill.
6. Ejecutar sus criterios de aceptación y las pruebas disponibles.
7. Registrar el resultado en `Spec/EXECUTION_LOG.md`.
8. Entregar el reporte y detenerse. No comenzar la siguiente skill.

## Prompt de inicio recomendado

```text
Lee completamente AGENTS.md, PromptMaster.md, Spec/README.md y Skill/README.md.
Después ejecuta únicamente Skill/K-001-preflight-y-linea-base.md.
No avances a K-002.

Antes de modificar archivos, inspecciona la rama, el estado Git, la configuración,
las migraciones, las rutas y las pruebas existentes. Conserva cambios ajenos.

Al terminar, reporta: archivos modificados, decisiones tomadas, comandos ejecutados,
pruebas, resultados, riesgos y bloqueos. Registra la ejecución en
Spec/EXECUTION_LOG.md. No hagas commit, push ni merge.
```

## Principios invariables

- Seguridad y conservación documental antes que nuevas pantallas.
- Autorización del lado del servidor para cada operación.
- Historial institucional inmutable; desactivar antes que borrar.
- Migraciones reversibles y sin pérdida silenciosa de datos.
- Reglas de negocio verificadas con pruebas.
- Una skill por ejecución.
- Toda decisión institucional desconocida se documenta y se bloquea; no se inventa.

## Definición global de terminado

Una skill se considera terminada cuando:

- Cumple todos sus criterios de aceptación.
- No rompe pruebas existentes.
- Incluye pruebas nuevas para el comportamiento introducido.
- No deja secretos ni datos reales versionados.
- Actualiza la documentación afectada.
- Registra evidencia reproducible de la ejecución.
