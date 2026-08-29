---
id: K-009
nombre: Estabilizar previsualización y postergar Drive
fase: 4
estado: PENDIENTE
---

## Objetivo

Ofrecer previsualización segura para Word/PDF y retirar Google Drive del flujo activo del MVP.

## Entradas

- `DEC-005` postergada en `Spec/S01-vision-alcance.md`
- `Spec/S02-diagnostico-errores.md`: `ERR-014`, `ERR-015`, `ERR-026`
- `Spec/S08-arquitectura-convenciones.md`
- `Spec/S09-seguridad-privacidad.md`

## Salidas

- Scripts de preview cargados mediante Vite o stack válido.
- Preview PDF/DOCX privado o degradación clara a descarga.
- Drive deshabilitado en navegación y flujo activo, sin eliminar credenciales o datos sin autorización.
- Configuración completa sin secretos versionados.
- Pruebas de OAuth state, MIME, tamaño y errores.

## Restricciones

- No publicar archivos para que Google Viewer los lea.
- No mostrar tokens ni excepciones de Google.
- No ampliar scopes más allá de lo necesario.
- No invertir trabajo en reparar Drive durante el MVP.

## Procedimiento

1. Confirmar que `DEC-005` permanece postergada.
2. Corregir carga de scripts y preview local.
3. Retirar enlaces activos de Drive sin borrar datos o historial.
4. Manejar PDF, DOC y DOCX admitidos; rechazar otros.
5. Implementar mensajes seguros y degradación a descarga.
6. Probar preview, descarga y archivo inválido.

## Criterios de aceptación

- Dado un DOCX autorizado, cuando se abre, entonces el preview funciona sin URL pública o permite descarga clara.
- Dado un usuario del MVP, cuando crea una planificación, entonces no se le dirige a OAuth/Drive.
- Dado un archivo no permitido, cuando se selecciona, entonces no se almacena.
- No se avanzó a K-010.
