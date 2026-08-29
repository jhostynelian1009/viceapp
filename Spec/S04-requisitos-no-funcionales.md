# S04 — Requisitos no funcionales

## Seguridad

- `RNF-001`: Toda operación protegida requiere autenticación y autorización del servidor.
- `RNF-002`: No se expondrán rutas directas a documentos privados.
- `RNF-003`: Las contraseñas se almacenarán con el hasher de Laravel.
- `RNF-004`: Formularios web usarán protección CSRF y validación de entrada.
- `RNF-005`: Secretos y tokens solo se configurarán mediante variables de entorno.

## Integridad y trazabilidad

- `RNF-010`: Las transiciones de estado serán atómicas.
- `RNF-011`: La eliminación de catálogos no destruirá registros históricos.
- `RNF-012`: Las fechas de auditoría se registrarán en UTC y se mostrarán con la zona institucional configurada.
- `RNF-013`: Las migraciones serán reversibles o incluirán un plan de recuperación documentado.

## Rendimiento

- `RNF-020`: Los listados usarán paginación y consultas con relaciones precargadas.
- `RNF-021`: Los filtros frecuentes tendrán índices adecuados.
- `RNF-022`: La descarga no cargará archivos grandes innecesariamente en memoria.
- `RNF-023`: La generación de reportes extensos podrá delegarse a colas si supera límites operativos.

## Usabilidad y accesibilidad

- `RNF-030`: La interfaz será responsive desde 360 px de ancho.
- `RNF-031`: Los estados no dependerán únicamente del color.
- `RNF-032`: Formularios tendrán etiquetas, errores asociados y navegación por teclado.
- `RNF-033`: Mensajes y términos institucionales estarán en español coherente.
- `RNF-034`: Las acciones destructivas o irreversibles solicitarán confirmación adecuada.

## Compatibilidad y mantenibilidad

- `RNF-040`: Soportar PHP 8.2+ y Laravel 12 según `composer.json`.
- `RNF-041`: Mantener compatibilidad con MySQL/MariaDB configurados por `.env`.
- `RNF-042`: El frontend deberá compilar mediante `npm ci && npm run build` sin depender de `node_modules` versionado.
- `RNF-043`: El código nuevo seguirá convenciones de Laravel y formato Pint.
- `RNF-044`: Las reglas de negocio importantes se representarán con enums, value objects, servicios o policies, no con cadenas dispersas.

## Calidad y operación

- `RNF-050`: Cada corrección crítica tendrá prueba automatizada.
- `RNF-051`: La suite no utilizará la base de datos de desarrollo o producción.
- `RNF-052`: El despliegue incluirá respaldo y procedimiento de rollback.
- `RNF-053`: Los errores de producción no expondrán excepciones ni secretos al usuario.
- `RNF-054`: El sistema generará logs útiles sin almacenar contraseñas, tokens ni contenido sensible innecesario.
