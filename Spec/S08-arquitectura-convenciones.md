# S08 — Arquitectura y convenciones

## Arquitectura objetivo

Se mantiene un monolito modular Laravel MVC:

- **HTTP:** controladores delgados y Form Requests.
- **Autorización:** middleware, policies y permissions.
- **Dominio:** enums y servicios para transiciones, versiones y reportes.
- **Persistencia:** modelos Eloquent, scopes y transacciones.
- **Presentación:** Blade, componentes y Tailwind.
- **Integraciones:** adaptadores separados para almacenamiento y exportación; Drive queda fuera del MVP.
- **Procesamiento asíncrono:** colas para notificaciones o reportes pesados cuando sea necesario.

## Servicios previstos

- `PlanningWorkflowService`
- `PlanningVersionService`
- `PlanningFileService`
- `ReportService`
- `AuditService`

Los nombres exactos pueden adaptarse a convenciones existentes, pero no duplicar reglas de transición en controladores y vistas.

## Convenciones

- Form Requests para validaciones no triviales.
- Enums PHP para estados y decisiones.
- Policies por recurso.
- Transacciones para carga + versión + cambio de estado.
- Eventos/listeners o notificaciones para efectos posteriores, sin ocultar la regla principal.
- Consultas paginadas y `with()` para evitar N+1.
- Fechas mediante Carbon y zona configurada.
- Variables locales en `.env`, nunca defaults personales en `config/`.

## Dependencias

Conservar inicialmente:

- Laravel y Breeze.
- Spatie Permission.
- Dompdf y PHPWord mientras los formatos sean requeridos.
- Google API Client no es necesario para el MVP; conservarlo deshabilitado hasta una solicitud institucional posterior.

No añadir paquetes cuando Laravel ya resuelva la necesidad de forma clara.

## Fronteras

- El módulo académico no gestiona estudiantes ni calificaciones.
- El almacenamiento no decide permisos; recibe una solicitud ya autorizada.
- La vista no decide transiciones permitidas; consulta reglas del dominio.
- Los reportes no deben construir consultas diferentes entre pantalla y descarga.
