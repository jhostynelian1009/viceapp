---
id: K-001
nombre: Preflight y línea base reproducible
fase: 1
estado: PENDIENTE
---

## Objetivo

Establecer una línea base verificable del repositorio antes de corregir comportamiento, sin perder datos ni cambios existentes.

## Responsabilidad

Inventariar entorno, deuda del repositorio, migraciones, configuración y pruebas; corregir únicamente fallas de reproducibilidad que no cambien reglas de negocio.

## Entradas

- `Spec/S02-diagnostico-errores.md`
- `Spec/S04-requisitos-no-funcionales.md`
- `Spec/S10-pruebas-calidad.md`
- `Spec/S12-roadmap-aceptacion.md`

## Salidas

- Informe de preflight.
- Defaults de configuración independientes del equipo local.
- `.env.example` documentado sin secretos.
- Inventario de archivos/datos versionados que requieren limpieza posterior.
- Línea base de tests, formato y build.

## Restricciones

- No eliminar documentos, `.env`, historial Git ni bases locales.
- No instalar versiones nuevas de framework.
- No corregir módulos funcionales en esta skill.

## Procedimiento

1. Revisar rama, remotos, estado Git y cambios ajenos.
2. Inventariar PHP, Composer, Node, npm, DB y extensiones requeridas.
3. Revisar `.gitignore`, archivos rastreados y tamaño del repositorio.
4. Restaurar defaults genéricos de configuración; dejar puerto/nombre local en `.env`.
5. Revisar scripts de instalación y README contra el comportamiento real.
6. Ejecutar suite, Pint y build sin modificar tests para ocultar fallas.
7. Clasificar fallas como previas o introducidas.
8. Registrar evidencia y detenerse.

## Criterios de aceptación

- Dado un clon limpio, cuando se siguen las instrucciones, entonces no necesita editar `config/database.php` para usar otro puerto.
- Dado el repositorio actual, cuando se inventarían archivos sensibles, entonces ninguno se elimina sin autorización.
- Dada la línea base, cuando se reporta, entonces contiene comandos, versiones y resultados reproducibles.
- No se avanzó a K-002.
