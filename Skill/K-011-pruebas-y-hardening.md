---
id: K-011
nombre: Completar pruebas y hardening
fase: 5
estado: PENDIENTE
---

## Objetivo

Demostrar que los requisitos críticos se cumplen y endurecer la configuración antes del despliegue.

## Entradas

- `Spec/S04-requisitos-no-funcionales.md`
- `Spec/S09-seguridad-privacidad.md`
- `Spec/S10-pruebas-calidad.md`
- Resultados de K-001 a K-010

## Salidas

- Matriz de trazabilidad requisito–test.
- Tests de autorización, flujo, archivos, reportes y migración.
- Validación MySQL/MariaDB adicional a SQLite.
- Cabeceras y configuración segura.
- Auditorías Composer/npm y análisis de logs/secretos.

## Restricciones

- No desactivar tests para obtener verde.
- No ejecutar pruebas contra producción.
- No corregir vulnerabilidades actualizando dependencias mayores sin analizar compatibilidad.

## Procedimiento

1. Mapear `ERR`, `RF`, `RNF`, `SEC` y `CA` a pruebas.
2. Completar casos positivos, negativos y de aislamiento.
3. Ejecutar migración en SQLite y MySQL/MariaDB de testing.
4. Ejecutar Pint, build y auditorías.
5. Configurar cabeceras/cookies/errores de producción.
6. Buscar secretos y archivos sensibles rastreados.
7. Documentar cobertura residual y riesgos aceptados.

## Criterios de aceptación

- Todas las correcciones P0 tienen pruebas automatizadas.
- La suite completa, Pint y build terminan sin fallas.
- Las auditorías no presentan vulnerabilidades conocidas sin decisión documentada.
- No hay secretos ni documentos reales nuevos en Git.
- No se avanzó a K-012.
