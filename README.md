<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

# Gestor de Planificaciones Docentes (GPD)

> **Estado actual:** prototipo en proceso de estabilización. No debe utilizarse con
> documentos institucionales reales hasta completar las correcciones P0 definidas
> en `Spec/S12-roadmap-aceptacion.md`.

## Desarrollo guiado por Spec as a Skill

La modernización del proyecto se ejecuta con Antigravity mediante:

- `AGENTS.md`: reglas obligatorias del repositorio.
- `PromptMaster.md`: contexto y prompt inicial.
- `Spec/`: fuente de verdad, diagnóstico y requisitos.
- `Skill/`: runbooks K-001 a K-012.
- `.agents/skills/`: skills nativas descubribles por Antigravity.

Comienza leyendo `PromptMaster.md` y ejecutando únicamente K-001.

## Visión General

Este proyecto, desarrollado en Laravel, tiene como objetivo digitalizar y automatizar el flujo de trabajo de creación, revisión, aprobación y archivo de las planificaciones docentes. La plataforma está diseñada para ser utilizada por tres roles principales: Docente, Secretaría y Vicerrector, cada uno con permisos y vistas específicas para sus responsabilidades.

## Cómo Empezar

Sigue estos pasos para clonar y configurar el proyecto en tu entorno de desarrollo local.

### 1. Clonar el Repositorio
```bash
git clone <URL_DEL_REPOSITORIO>
cd <NOMBRE_DEL_DIRECTORIO>
```

### 2. Instalar Dependencias
Asegúrate de tener Composer y Node.js instalados en tu máquina.

```bash
# Instalar dependencias de PHP
composer install

# Instalar dependencias de Node.js
npm install
```

### 3. Configuración del Entorno
Copia el archivo de ejemplo `.env.example` para crear tu propio archivo de configuración de entorno.

```bash
cp .env.example .env
```
A continuación, genera la clave de la aplicación, que es fundamental para la seguridad de Laravel.

```bash
php artisan key:generate
```

### 4. Base de Datos
Ejecuta las migraciones para crear la estructura de la base de datos y los seeders para poblarla con los datos iniciales (roles y usuarios de prueba).

```bash
php artisan migrate --seed
```

### 5. Iniciar los Servidores de Desarrollo
Finalmente, inicia el servidor de desarrollo de Laravel y el compilador de assets de Vite.

```bash
# Iniciar el servidor de Laravel (generalmente en http://127.0.0.1:8000)
php artisan serve

# En una terminal separada, iniciar Vite para compilar los assets
npm run dev
```

¡Y listo! Ahora puedes acceder a la aplicación desde tu navegador.

## Credenciales de Usuario

Puedes utilizar las siguientes credenciales para acceder a la aplicación y probar los diferentes roles:

| Rol | Email | Contraseña |
| :--- | :--- | :--- |
| Docente | `docente@example.com` | `password` |
| Secretaria | `secretaria@example.com`| `password` |
| Vicerrector | `vicerrector@example.com`| `password` |
