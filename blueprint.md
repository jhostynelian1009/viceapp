# Gestor de Planificaciones Docentes (GPD)

## Visión General

Este proyecto tiene como objetivo digitalizar y automatizar el flujo de trabajo de creación, revisión, aprobación y archivo de las planificaciones docentes.

## Plan de Desarrollo

### Sprint 1: Configuración del Proyecto y Autenticación

- [x] **Instalar y configurar el paquete Spatie para roles y permisos.**
- [x] **Crear los roles (Docente, Secretaría, Vicerrector).**
- [x] **Crear el sistema de autenticación (vistas y controladores).**

### Sprint 2: Carga y Organización de Archivos

- [x] **Crear el modelo y la migración para las planificaciones.**
- [x] **Implementar la funcionalidad de carga de archivos.**
- [x] **Crear vistas para listar y organizar las planificaciones.**

### Sprint 3: Flujo de Trabajo y Visualización

- [x] **Implementar la lógica para cambiar el estado de las planificaciones.**
- [x] **Crear un visualizador de archivos en el navegador.**

### Sprint 4: Colaboración y Búsqueda

- [x] **Crear un sistema de notificaciones para los cambios de estado.**
- [x] **Añadir un campo de búsqueda para filtrar las planificaciones.**

### Sprint 5: Integración con Google Drive

- [x] **Configurar la API de Google Drive.**
- [x] **Implementar la carga de archivos desde Google Drive.**

### Sprint 6: Mejoras en la Interfaz y Experiencia de Usuario

- [x] **Rediseñar la interfaz para una apariencia más moderna y limpia.**
- [x] **Asegurar que el diseño sea responsivo y funcione en dispositivos móviles.**

### Sprint 7: Gestión de Docentes (CRUD)

- [x] **Crear el modelo y la migración para los docentes.**
- [x] **Definir rutas CRUD para docentes.**
- [x] **Implementar el `TeacherController` para gestionar las operaciones CRUD.**
- [x] **Crear la vista `index` para listar los docentes.**
- [x] **Crear la vista `create` con un formulario para añadir nuevos docentes.**
- [x] **Crear la vista `edit` con un formulario para actualizar la información de los docentes.**
- [x] **Implementar la lógica para eliminar docentes.**

### Sprint 8: Gestión de Áreas Académicas

- [ ] **Crear modelo y migración para Áreas Académicas (`Subject`).**
- [ ] **Implementar CRUD para que el rol `vicerrector` gestione las Áreas Académicas.**
- [ ] **Añadir enlace en la navegación para la gestión de Áreas Académicas (solo visible para `vicerrector`).**
- [ ] **Actualizar la migración de `plannings` para añadir la relación con `Subject`.**
- [ ] **Modificar el formulario de subida de planificaciones para incluir un selector de Área Académica.**
- [ ] **Mostrar el Área Académica en las vistas de listado de planificaciones.**

### Sprint 9: Generación de Reportes

- [ ] **Crear el `ReportController` para gestionar la lógica de los reportes.**
- [ ] **Definir la ruta para la vista de reportes.**
- [ ] **Diseñar la vista `reports/index.blade.php` con un formulario para seleccionar fechas.**
- [ ] **Implementar la lógica para consultar y agrupar las planificaciones por estado.**
- [ ] **Mostrar los resultados en una tabla dentro de la misma vista.**

### Mantenimiento y Configuración

- [x] **Generar clave de encriptación de la aplicación (`php artisan key:generate`) para resolver `Illuminate\Encryption\MissingAppKeyException`.**

## Credenciales de Usuario

| Rol | Email | Contraseña |
| :--- | :--- | :--- |
| Docente | `docente@example.com` | `password` |
| Secretaria | `secretaria@example.com`| `password` |
| Vicerrector | `vicerrector@example.com`| `password` |
