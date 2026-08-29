---
id: K-003
nombre: Cerrar autorización y gobierno de cuentas
fase: 2
estado: PENDIENTE
---

## Objetivo

Eliminar accesos indebidos por URL directa y controlar el ciclo de vida de cuentas institucionales.

## Entradas

- `Spec/S02-diagnostico-errores.md`: `ERR-001`–`ERR-006`
- `Spec/S05-roles-autorizacion.md`
- `Spec/S09-seguridad-privacidad.md`

## Salidas

- Registro público deshabilitado o restringido.
- Middleware de rol/permiso en rutas administrativas.
- Policies para Planning, Comment, User, Report y Notification.
- Estado activo/inactivo de usuario.
- Pruebas de autorización por rol y propiedad.

## Restricciones

- No confiar en enlaces ocultos en Blade.
- No usar respuestas 404/403 inconsistentes que filtren información sensible.
- No permitir que un usuario se autoasigne roles.

## Procedimiento

1. Construir matriz ruta–acción–rol.
2. Cerrar registro público y definir alta administrativa.
3. Crear policies y registrarlas según Laravel 12.
4. Aplicar middleware a grupos de rutas.
5. Autorizar propiedad, ámbito y estado en cada mutación.
6. Implementar desactivación sin borrar historial.
7. Añadir pruebas positivas y negativas, incluyendo IDs ajenos.

## Criterios de aceptación

- Dado un docente, cuando llama rutas de usuarios o reportes globales, entonces recibe 403.
- Dadas dos cuentas docentes, cuando una usa el ID de la otra, entonces no puede ver, descargar, comentar ni cambiar estado.
- Dada una notificación ajena, cuando se intenta marcarla, entonces permanece sin cambios.
- Dada una cuenta inactiva, cuando intenta autenticarse, entonces se rechaza el acceso.
- No se avanzó a K-004.
