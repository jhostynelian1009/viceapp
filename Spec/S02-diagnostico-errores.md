# S02 — Diagnóstico y catálogo de errores

## Estado general

El repositorio es un prototipo Laravel reutilizable, pero no está listo para producción. La prioridad es estabilizar y asegurar antes de añadir módulos.

## Errores críticos

| ID | Hallazgo verificable | Impacto | Corrección esperada |
|---|---|---|---|
| `ERR-001` | Las rutas CRUD de docentes no exigen rol administrativo. | Un docente puede crear, editar o eliminar usuarios mediante URL directa. | Restringir rutas y aplicar policy sobre el usuario objetivo. |
| `ERR-002` | Los reportes solo exigen autenticación. | Exposición de datos institucionales. | Autorizar por rol y permiso. |
| `ERR-003` | `updateStatus` no comprueba propiedad cuando actúa un docente. | IDOR: un docente puede enviar una planificación ajena. | `PlanningPolicy` y validación de transición. |
| `ERR-004` | Crear comentarios no comprueba acceso a la planificación. | Comentarios no autorizados y notificaciones indebidas. | Autorizar `comment` sobre la planificación. |
| `ERR-005` | Marcar notificación como leída no verifica propietario. | Manipulación de notificaciones ajenas. | Consultar la notificación desde el usuario autenticado. |
| `ERR-006` | El registro público crea usuarios sin rol. | Cuentas huérfanas y acceso parcial no controlado. | Deshabilitar registro público; alta administrativa. |
| `ERR-007` | Los seeders crean roles duplicados por mayúsculas y tilde. | Permisos inconsistentes. | Normalizar identificadores de roles y migrar datos. |
| `ERR-008` | El borrado de usuario o materia elimina planificaciones en cascada. | Pérdida de archivo institucional. | Desactivación/soft delete y FKs restrictivas o nulas. |
| `ERR-009` | Los documentos se almacenan en el disco público. | Acceso posible sin pasar por autorización. | Disco privado y descarga/preview controlados. |
| `ERR-010` | Hay documentos cargados versionados en Git. | Fuga de datos y crecimiento del repositorio. | Retirar del índice/historial según autorización y reforzar `.gitignore`. |

## Errores altos

| ID | Hallazgo verificable | Impacto | Corrección esperada |
|---|---|---|---|
| `ERR-011` | La vista de notificaciones espera `title/message`, pero la notificación guarda otros campos. | Errores de render o campana vacía. | Definir contrato único de payload. |
| `ERR-012` | El formulario de reportes usa estados ingleses y la BD estados españoles. | Los filtros devuelven resultados incorrectos. | Enum/códigos internos únicos y etiquetas traducidas. |
| `ERR-013` | El rango final del reporte usa la fecha a medianoche. | Omite registros del último día. | Usar inicio/fin de día o filtro por fecha. |
| `ERR-014` | El layout no renderiza `@stack('scripts')`. | No se ejecutan preview DOCX ni Google Picker. | Añadir stack o integrar scripts con Vite. |
| `ERR-015` | `config/google.php` no declara `developer_key`. | Google Picker recibe configuración incompleta. | Configurar y validar variables requeridas. |
| `ERR-016` | `SubjectSeeder` no se invoca desde `DatabaseSeeder`. | Instalación nueva sin materias. | Integrar seeder idempotente. |
| `ERR-017` | Usuarios demo no se marcan como verificados. | El dashboard protegido puede redirigirlos a verificación. | Definir estrategia de verificación para cuentas administrativas. |
| `ERR-018` | Un documento rechazado no puede reemplazarse ni versionarse. | Puede reenviarse sin corregir o duplicarse. | Versionado y reemplazo controlado. |
| `ERR-019` | Aprobar/rechazar no registra revisor, fecha ni motivo. | Sin trazabilidad de decisiones. | Tabla de revisiones e historial de eventos. |
| `ERR-020` | El README declara funcionalidades como completas aunque existen fallas. | Instalación y evaluación engañosas. | Actualizar documentación con estado verificable. |

## Errores medios y deuda técnica

| ID | Hallazgo | Corrección esperada |
|---|---|---|
| `ERR-021` | Puerto y nombre de BD locales fueron cambiados como defaults de `config/database.php`. | Restaurar defaults del framework; configurar en `.env`. |
| `ERR-022` | `UserSeeder` no es idempotente. | Usar `updateOrCreate`/`firstOrCreate` de forma segura. |
| `ERR-023` | El test de `/` espera 200 aunque la ruta redirige. | Esperar redirección a login. |
| `ERR-024` | No hay tests del flujo principal ni de autorización. | Crear matriz de pruebas por rol y recurso. |
| `ERR-025` | El ZIP incluye `.git`, `node_modules`, cachés, sesiones y `.env`. | Distribuir únicamente fuente y locks. |
| `ERR-026` | `GoogleDriveServiceProvider` existe pero no está registrado y duplica configuración. | Deshabilitar Drive en el MVP y dejar su eventual retiro como cambio seguro y documentado. |
| `ERR-027` | La UI afirma que permite editar planificaciones, pero no hay `edit/update`. | Corregir texto o implementar edición/versionado. |
| `ERR-028` | Los comentarios no limitan longitud ni se exige motivo al rechazar. | Validar longitud y vincular rechazo con observación obligatoria. |
| `ERR-029` | La vista móvil contiene cierre de componente inconsistente. | Corregir componente Blade y probar navegación móvil. |
| `ERR-030` | Los reportes Word usan un nombre temporal compartido. | Usar archivo temporal único o respuesta en memoria. |

## Regla de cierre

Un error solo cambia a resuelto cuando existe evidencia en código y una prueba automatizada o verificación reproducible. Ocultar el enlace en la interfaz no resuelve errores de autorización.
