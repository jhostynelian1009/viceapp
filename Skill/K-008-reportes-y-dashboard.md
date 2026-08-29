---
id: K-008
nombre: Corregir reportes y dashboard
fase: 4
estado: PENDIENTE
---

## Objetivo

Entregar indicadores y exportaciones autorizadas, consistentes y reproducibles.

## Entradas

- `DEC-004` confirmada en `Spec/S01-vision-alcance.md`
- `Spec/S02-diagnostico-errores.md`: `ERR-002`, `ERR-012`, `ERR-013`, `ERR-030`
- `Spec/S03-requisitos-funcionales.md`: `RF-050`–`RF-055`

## Salidas

- Servicio/objeto de filtros compartido entre vista y descarga.
- Estados internos coherentes.
- Rangos de fecha inclusivos y validados.
- Dashboard de Vicerrectorado con aprobadas, pendientes y rechazadas.
- Resumen administrativo de solo consulta para Secretaría.
- Exportación solo si se mantiene como mejora secundaria.
- Pruebas de filtros y autorización.

## Restricciones

- No agregar indicadores distintos de aprobadas, pendientes y rechazadas como requisito obligatorio.
- No cargar conjuntos sin límite en memoria cuando puedan crecer.
- No revelar datos globales a docentes.

## Procedimiento

1. Implementar el resumen confirmado y la definición de pendiente.
2. Crear validación única de filtros.
3. Reutilizar la misma consulta en pantalla y exportación.
4. Corregir códigos de estado y límites de fecha.
5. Añadir indicadores con consultas eficientes.
6. Generar archivos temporales únicos o respuestas en memoria.
7. Probar exactitud con datos en bordes de fecha y distintos roles.

## Criterios de aceptación

- Si se conserva la exportación, dados filtros idénticos, pantalla y archivo contienen los mismos registros.
- Dado un borrador, cuando se calcula el resumen oficial, entonces no aumenta el contador de pendientes.
- Dado el último día del rango, cuando hay registros a las 23:59, entonces se incluyen.
- Dado un docente, cuando intenta un reporte global, entonces recibe 403.
- No se avanzó a K-009.
