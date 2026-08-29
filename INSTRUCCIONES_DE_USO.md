# Instrucciones de integración y uso

## Contenido del paquete

Este paquete contiene la documentación **Spec as a Skill** para modernizar el Sistema de Gestión de Planificaciones Semanales de la U.E. Fiscomisional 10 de Agosto.

- `AGENTS.md`: reglas obligatorias del repositorio.
- `PromptMaster.md`: contexto general y prompt inicial.
- `Spec/`: diagnóstico, requisitos y arquitectura objetivo.
- `Skill/`: runbooks K-001 a K-012.
- `.agents/skills/`: adaptadores nativos para Antigravity.
- `README.md`: descripción del proyecto y acceso a la metodología.

## Cómo incorporarlo

1. Descomprimir el ZIP.
2. Copiar todo su contenido en la raíz del proyecto Laravel `viceapp`.
3. Conservar la carpeta oculta `.agents`.
4. Si Git muestra cambios, revisar que solo correspondan a esta documentación.
5. No reemplazar `.env`, código fuente, migraciones ni documentos cargados.

La estructura final esperada es:

```text
viceapp/
├── AGENTS.md
├── PromptMaster.md
├── README.md
├── Spec/
├── Skill/
└── .agents/
    └── skills/
```

## Inicio en Antigravity

Abrir la carpeta raíz `viceapp` como workspace y enviar:

```text
Lee completamente AGENTS.md, PromptMaster.md, Spec/README.md y Skill/README.md.

Después ejecuta únicamente Skill/K-001-preflight-y-linea-base.md.
No avances a K-002.

Antes de modificar archivos, inspecciona la rama, el estado Git, la configuración,
las migraciones, las rutas y las pruebas existentes. Conserva cambios ajenos.

Al terminar, reporta archivos modificados, decisiones, comandos, pruebas,
resultados, riesgos y bloqueos. Registra la ejecución en
Spec/EXECUTION_LOG.md. No hagas commit, push ni merge.
```

## Reglas confirmadas

- Planificaciones semanales en Word o PDF.
- Secretaría administra, pero no aprueba ni rechaza.
- Vicerrectorado es la única autoridad de revisión y decisión.
- Clasificación por área académica, docente, curso, paralelo y asignatura.
- Resumen de aprobadas, pendientes y rechazadas.
- Los borradores no cuentan como pendientes oficiales.
- Google Drive queda fuera del MVP.

## Orden de trabajo

Ejecutar K-001 a K-012 en orden. Cada skill debe terminar, probarse y registrarse antes de comenzar la siguiente. Antigravity no debe hacer `commit`, `push`, `merge` ni desplegar sin autorización expresa.
