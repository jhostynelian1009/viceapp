---
id: K-010
nombre: Mejorar interfaz y accesibilidad
fase: 4
estado: PENDIENTE
---

## Objetivo

Unificar la experiencia por rol y corregir inconsistencias visuales sin cambiar la identidad institucional.

## Entradas

- `Spec/S03-requisitos-funcionales.md`
- `Spec/S04-requisitos-no-funcionales.md`: `RNF-030`–`RNF-034`
- `Spec/S02-diagnostico-errores.md`: `ERR-027`, `ERR-029`

## Salidas

- Navegación coherente en escritorio y móvil.
- Dashboard y acciones alineadas con permisos reales.
- Formularios accesibles y mensajes consistentes.
- Estados con texto/icono además de color.
- Componentes Blade reutilizables y compilables.

## Restricciones

- No resolver permisos solo ocultando elementos.
- No cambiar paleta/logo institucional sin autorización.
- No afirmar funciones inexistentes.

## Procedimiento

1. Inventariar pantallas y acciones por rol.
2. Corregir componentes Blade y navegación responsive.
3. Alinear textos con el flujo implementado.
4. Unificar badges, alertas, tablas, vacíos y validaciones.
5. Verificar teclado, foco, contraste y 360/768/1280 px.
6. Ejecutar build y smoke test por rol.

## Criterios de aceptación

- Dado cualquier rol, cuando abre navegación móvil, entonces no hay errores Blade ni enlaces no autorizados.
- Dado un estado, cuando se visualiza sin color, entonces sigue siendo comprensible.
- Dado un formulario inválido, cuando se envía, entonces el error identifica campo y solución.
- No se avanzó a K-011.
