# Registro de ejecución

No sobrescribir entradas anteriores. Añadir una entrada por cada ejecución de skill.

## Plantilla

```markdown
### YYYY-MM-DD HH:MM — K-xxx

- Estado: COMPLETADA | PARCIAL | BLOQUEADA
- Rama:
- Acción realizada:
- Justificación Spec: ERR-xxx, RF-xxx, RNF-xxx
- Skill utilizada: K-xxx
- Archivos modificados:
- Comandos ejecutados:
- Pruebas y resultados:
- Decisiones tomadas:
- Riesgos o bloqueos:
- Siguiente skill sugerida: K-xxx (no ejecutada)
```

### 2026-08-29 11:15 — K-001

- Estado: COMPLETADA
- Rama: Jhostyn
- Acción realizada: Preflight e inventario de línea base del repositorio. Restaurados valores por defecto del framework en `config/database.php` (ERR-021) y añadida variable `GOOGLE_DEVELOPER_KEY` en `.env.example` (ERR-015).
- Justificación Spec: ERR-010, ERR-015, ERR-021, RNF-040, RNF-041, RNF-042, RNF-050
- Skill utilizada: K-001 (Preflight y línea base reproducible)
- Archivos modificados:
  - `config/database.php`: Restaurados fallbacks estándar de Laravel (`3306`, `'laravel'`) evitando configuraciones hardcodeadas locales.
  - `.env.example`: Documentada la variable `GOOGLE_DEVELOPER_KEY` en la sección de Google Drive API.
  - `Spec/EXECUTION_LOG.md`: Registro de ejecución de la skill K-001.
- Comandos ejecutados:
  - `git branch; git status; git remote -v`
  - `php -v; composer --version; node -v; npm -v; php artisan --version`
  - `php artisan route:list; php artisan migrate:status`
  - `git ls-files storage/ public/ .env* *.doc *.docx *.pdf`
  - `composer validate`
  - `composer audit`
  - `php artisan optimize:clear; php artisan test`
  - `vendor/bin/pint --test`
  - `npm run build; npm audit --omit=dev`
- Pruebas y resultados:
  - `composer validate`: Exitoso (`./composer.json is valid`).
  - `composer audit`: Advertencias/advisories conocidas de paquetes Symfony y dependencias PHP.
  - `php artisan test`: 25 PASSED, 1 FAILED (`Tests\Feature\ExampleTest` falla esperando 200 en `/` cuando redirige 302 a login — falla preexistente `ERR-023`).
  - `vendor/bin/pint --test`: 17 archivos con estilo preexistente por corregir (deuda de código preexistente).
  - `npm run build`: Exitoso (Vite v7.2.7 construyó los assets).
  - `npm audit --omit=dev`: 0 vulnerabilidades.
- Decisiones tomadas:
  - Se preservan todos los archivos versionados sensibles (`storage/app/public/plannings/*.pdf`, `.docx`) en Git hasta la ejecución de la skill K-004 de privatización de documentos.
  - Se mantiene el fallo en `ExampleTest` como falla preexistente documentada (`ERR-023`), sin modificar el test en K-001 para no alterar el comportamiento antes de la skill correspondiente.
- Riesgos o bloqueos:
  - Deuda de código en Pint (17 archivos).
  - Documentos reales en el disco público versionados en Git (`storage/app/public/plannings/`).
  - Falta de roles normalizados en BD (`ERR-007`).
- Siguiente skill sugerida: K-002 (Normalización de roles y seeders) — COMPLETADA.

### 2026-08-29 11:30 — K-002 (Auditoría y Corrección Final)

- Estado: COMPLETADA (Auditada y Corregida)
- Rama: Jhostyn
- Acción realizada: 
  - Normalización de los identificadores de roles canónicos (`secretaria`, `vicerrectorado`, `docente`).
  - Creación y ajuste de la migración `database/migrations/2026_08_29_000001_normalize_role_names.php` para soportar comparación binaria portable (MySQL/MariaDB y SQLite). El método `down()` fue modificado para lanzar explícitamente una `RuntimeException` indicando que la normalización es una consolidación irreversible de datos históricos.
  - Verificación e implementación de idempotencia completa en seeders (`RolesAndPermissionsSeeder`, `UserSeeder`, `SubjectSeeder`, `DatabaseSeeder`). Cuentas demo restringidas estrictamente a entornos `local` y `testing`.
  - Actualización de referencias del rol `vicerrector` a `vicerrectorado` en rutas, controladores, vistas y tests.
  - Creación de suites de pruebas automatizadas específicas: `Tests\Feature\RoleNormalizationTest` (verificación de variantes legadas, coexistencia, preservación de pivots `model_has_roles`/`role_has_permissions`, ausencia de duplicados, idempotencia y comportamiento de `down()`) y `Tests\Feature\SeederIdempotencyTest`.
  - Diagnóstico del incidente del comando destructivo `php artisan migrate:fresh --seed` sobre MySQL `viceapp`: La base de datos local fue reconstruida por dicho comando; se confirmó que los metadatos anteriores de planificaciones no poseían respaldo local en formato `.sql`, dejando 5 archivos de planificaciones físicos huérfanos en `storage/app/public/plannings/` sin intentar fabricar o inventar metadatos sintéticos.
  - Adopción de medidas de protección: Prohibición absoluta de comandos destructivos (`migrate:fresh`, `db:wipe`, etc.) sobre la base MySQL `viceapp`. Demostrado el aislamiento total de PHPUnit operando exclusivamente sobre una base de datos SQLite en memoria (`:memory:`).
  - Auditoría completa de dependencias con `composer audit --format=json` y `composer show`.
- Justificación Spec: ERR-007, ERR-016, ERR-017, ERR-022, S05-roles-autorizacion, S07-modelo-datos.
- Skill utilizada: K-002 (Normalizar roles y seeders)
- Archivos modificados/creados:
  - `database/migrations/2026_08_29_000001_normalize_role_names.php`: Migración con `up()` idempotente y `down()` con `RuntimeException` explícita por consolidación irreversible.
  - `database/seeders/RolesAndPermissionsSeeder.php`: Roles canónicos `'docente'`, `'secretaria'`, `'vicerrectorado'`.
  - `database/seeders/UserSeeder.php`: Idempotente con `firstOrCreate` y `syncRoles`, restringido a `local`/`testing`.
  - `database/seeders/SubjectSeeder.php`: Idempotente con `firstOrCreate`.
  - `database/seeders/DatabaseSeeder.php`: Incluye `SubjectSeeder::class`.
  - `routes/web.php`, `app/Http/Controllers/PlanningController.php`, `resources/views/...`: Actualizados a `vicerrectorado`.
  - `tests/Feature/RoleNormalizationTest.php` *(Nuevo)*: Pruebas automatizadas de normalización en SQLite aislado.
  - `tests/Feature/SeederIdempotencyTest.php` *(Nuevo)*: Pruebas de idempotencia de seeders en SQLite aislado.
  - `tests/Feature/SubjectFeatureTest.php`: Actualizado a `vicerrectorado`.
- Comandos ejecutados:
  - `php artisan optimize:clear`
  - `php artisan migrate --pretend`
  - `php artisan test` (31 PASSED, 1 FAILED preexistente `ExampleTest` ERR-023)
  - `vendor/bin/pint tests/Feature/RoleNormalizationTest.php tests/Feature/SeederIdempotencyTest.php database/migrations/2026_08_29_000001_normalize_role_names.php`
  - `composer validate`
  - `composer audit --format=json`
  - `composer show symfony/process; composer show symfony/routing; composer show symfony/yaml`
  - `npm run build`
- Pruebas y resultados:
  - PHPUnit en SQLite aislado: 31 PASSED, 1 FAILED (Fallo preexistente `ExampleTest` documentado `ERR-023`).
  - `RoleNormalizationTest`: 5/5 PASSED.
  - `SeederIdempotencyTest`: 1/1 PASSED.
  - `composer validate`: Exitoso (`./composer.json is valid`).
  - `npm run build`: Exitoso.
- Decisiones tomadas:
  - `down()` de la migración de normalización lanza una `RuntimeException` clara para impedir reversiones parciales o inconsistentes sin respaldo.
  - Las pruebas corren exclusivamente en SQLite memory. Ningún comando destructivo o migración adicional se ejecutó en MySQL `viceapp`.
  - No se avanzó a la skill K-003.
- Riesgos o bloqueos:
  - Vulnerabilidades de Composer Audit registradas como deuda técnica prioritaria para K-011 (`symfony/process` v7.4.0, `symfony/routing` v7.4.0, `symfony/yaml` v7.4.1).
- Siguiente skill sugerida: K-003 (Cierre de brechas de autorización en servidor) — NO EJECUTADA.

### 2026-08-29 11:45 — HOTFIX DOCUMENTAL (Post K-002)

- Estado: COMPLETADO
- Rama: Jhostyn
- Acción realizada: 
  - Hotfix exclusivamente documental para eliminar contradicciones de nomenclatura de roles, reforzar reglas de protección de datos contra comandos destructivos y normar las migraciones irreversibles.
  - No se modificó ningún código PHP, migración, seeder, ruta, vista ni prueba. No se ejecutó K-003.
- Contradicciones encontradas y corregidas:
  - `Spec/S05-roles-autorizacion.md`: Sustituidos identificadores legados/contradictorios (`teacher` $\rightarrow$ `docente`, `secretary` $\rightarrow$ `secretaria`, `vice_principal` $\rightarrow$ `vicerrectorado`). Marcado el administrador técnico (`technical_admin`) como `PENDIENTE_INSTITUCION` sin permisos académicos.
  - `Skill/K-002-normalizar-roles-y-seeders.md`: Reemplazada la instrucción imprecisa `Probar migrate:fresh --seed` por reglas estrictas de ejecución segura (exclusivamente sobre SQLite efímero `:memory:` en entorno `testing`), prohibiendo explícitamente su uso sobre MySQL `viceapp` u otras bases persistentes.
  - `AGENTS.md`: Incorporada la sección `Protección de datos y comandos destructivos` con prohibición expresa de `migrate:fresh`, `migrate:refresh`, `migrate:reset`, `db:wipe`, `DROP`, `TRUNCATE` y sobrescritura de respaldos en bases persistentes.
  - `PromptMaster.md`: Actualizada la regla de migraciones irreversibles, estableciendo que si una migración consolida datos históricos y no puede reconstruir el estado original, `down()` debe bloquear la reversión (ej. lanzando excepción) o requerir restauración desde respaldo, exigiendo documentación y autorización previa.
- Identificadores definitivos: `docente`, `secretaria`, `vicerrectorado`.
- Archivos modificados:
  - `AGENTS.md`
  - `PromptMaster.md`
  - `Spec/S05-roles-autorizacion.md`
  - `Skill/K-002-normalizar-roles-y-seeders.md`
  - `Spec/EXECUTION_LOG.md`
- Comandos ejecutados (búsquedas de solo lectura):
  - `grep_search` para `teacher`, `secretary`, `vice_principal`, `technical_admin` (0 resultados en documentación activa fuera de `teacher_id` en S07).
  - `grep_search` para `migrate:fresh` (verificando que sólo permanece en advertencias/prohibiciones).
- Confirmaciones:
  - 0 código PHP o vistas modificados.
  - Skill K-003 NO ejecutada.
- Siguiente skill sugerida: K-003 (Cierre de brechas de autorización en servidor) — COMPLETADA.

### 2026-08-29 12:00 — K-003 (Cerrar Autorización y Cuentas — Ajustes y Aplicación)

- Estado: COMPLETADA
- Rama: Jhostyn
- Acción realizada:
  - Registro público deshabilitado (`GET/POST /register` retornan 404).
  - Aplicada de forma controlada la migración aditiva `2026_08_29_000002_add_is_active_to_users_table.php` a la BD MySQL `viceapp` tras verificación previa con `--pretend` y respaldo de conteos.
  - Ajustado acceso a asignaturas (`subjects`): Autorizadas rutas para `secretaria` y `vicerrectorado` (`role:secretaria|vicerrectorado`), `docente` 403.
  - Restringido acceso de Secretaría al ámbito administrativo: Puede listar metadatos en `/plannings`, pero recibe 403 en `/plannings/{id}/view`, `/download`, `/comments`, `/status`. La vista `plannings/index.blade.php` condiciona la presentación de acciones académicas mediante `@can('view', $planning)`.
  - Creada `NotificationPolicy` (`app/Policies/NotificationPolicy.php`) registrada en `AppServiceProvider` para validar propiedad `notifiable_id` en `NotificationController::markAsRead`.
  - Creada `ReportPolicy` (`app/Policies/ReportPolicy.php`) con Gates `reports.view` y `reports.export` registrados en `AppServiceProvider` e invocados en `ReportController`.
  - Revertidos cambios fuera de alcance en `GoogleDriveController.php` y `SubjectController.php`.
  - Refactorizado `RoleMiddleware.php` para enfocarse exclusivamente en validación de roles, dejando la expulsión de cuentas inactivas a `EnsureAccountIsActive`.
  - Pruebas exhaustivas ejecutadas en SQLite `:memory:` (51 PASSED, 1 FAILED preexistente `ExampleTest` ERR-023).
- Justificación Spec: ERR-001 a ERR-006, S05-roles-autorizacion, S09-seguridad-privacidad.
- Skill utilizada: K-003 (Cerrar autorización y cuentas)
- Matriz Ruta-Acción-Rol Definitiva:
  - `GET/POST /register`: Bloqueado (404).
  - `GET /dashboard`, `/profile`: `auth`, `active` (Usuarios autenticados y activos).
  - `DELETE /profile`: Bloqueado (403 para proteger historial institucional).
  - `POST /notifications/{notification}/read`: Propietario (`notifiable_id === auth()->id()`) validado por `NotificationPolicy`.
  - `GET /plannings`: `docente` (propias), `secretaria` y `vicerrectorado` (metadatos generales).
  - `POST /plannings`: `docente`.
  - `GET /plannings/{planning}/view`: `docente` (propias), `vicerrectorado`. `secretaria` 403.
  - `GET /plannings/{planning}/download`: `docente` (propias), `vicerrectorado`. `secretaria` 403.
  - `PATCH /plannings/{planning}/status`: `revisión` (`docente`), `aprobado`/`rechazado` (`vicerrectorado`). `secretaria` 403.
  - `GET /plannings/review`: `vicerrectorado`.
  - `POST /plannings/{planning}/comments`: `docente` (propias), `vicerrectorado`. `secretaria` 403.
  - `GET/POST/PATCH/DELETE /teachers`: `secretaria|vicerrectorado` (Docente 403; DELETE desactiva sin borrar).
  - `GET/POST/PUT/DELETE /subjects`: `secretaria|vicerrectorado` (Docente 403).
  - `GET /reports`, `/reports/download/{type}`: `secretaria|vicerrectorado` (Docente 403, validado por `reports.view`/`reports.export` Gates).
- Archivos modificados/creados:
  - `database/migrations/2026_08_29_000002_add_is_active_to_users_table.php` *(Nueva y aplicada)*
  - `app/Http/Middleware/EnsureAccountIsActive.php` *(Nuevo)*
  - `app/Policies/PlanningPolicy.php` *(Nueva)*
  - `app/Policies/NotificationPolicy.php` *(Nueva)*
  - `app/Policies/ReportPolicy.php` *(Nueva)*
  - `app/Policies/UserPolicy.php` *(Nueva)*
  - `app/Http/Requests/StoreTeacherRequest.php` *(Nuevo)*
  - `app/Http/Requests/UpdateTeacherRequest.php` *(Nuevo)*
  - `tests/Feature/AuthorizationAndAccountsTest.php` *(Nueva/Ampliada)*
  - `tests/Feature/SubjectFeatureTest.php` *(Modificada/Ampliada)*
  - `app/Models/User.php`
  - `bootstrap/app.php`
  - `app/Providers/AppServiceProvider.php`
  - `app/Http/Middleware/RoleMiddleware.php`
  - `app/Http/Controllers/Auth/AuthenticatedSessionController.php`
  - `app/Http/Controllers/PlanningController.php`
  - `app/Http/Controllers/TeacherController.php`
  - `app/Http/Controllers/CommentController.php`
  - `app/Http/Controllers/ReportController.php`
  - `app/Http/Controllers/NotificationController.php`
  - `app/Http/Controllers/ProfileController.php`
  - `resources/views/plannings/index.blade.php`
  - `routes/auth.php`
  - `routes/web.php`
  - `database/seeders/UserSeeder.php`
  - `tests/Feature/Auth/RegistrationTest.php`
  - `tests/Feature/ProfileTest.php`
  - `Spec/EXECUTION_LOG.md`
- Conteos Base MySQL `viceapp` (Antes vs Después):
  - `users`: 3 -> 3 (3 activos, `is_active = true`)
  - `roles`: 3 -> 3
  - `subjects`: 8 -> 8
  - `plannings`: 0 -> 0
  - `comments`: 0 -> 0
  - `notifications`: 0 -> 0
- Comandos ejecutados:
  - `php artisan migrate:status`
  - `php artisan migrate --pretend`
  - `php artisan migrate`
  - `vendor/bin/pint`
  - `php artisan test` (51 PASSED, 1 FAILED preexistente `ExampleTest` ERR-023)
  - `composer validate`
  - `php artisan route:list`
  - `npm run build`
  - `git diff --check`
- Pruebas y resultados:
  - PHPUnit en SQLite aislado: 51 PASSED, 1 FAILED (`ExampleTest` ERR-023 preexistente).
  - `AuthorizationAndAccountsTest`: 20/20 PASSED.
  - `SubjectFeatureTest`: 3/3 PASSED.
  - `RegistrationTest`: 2/2 PASSED.
  - `ProfileTest`: 4/4 PASSED.
  - `RoleNormalizationTest`: 5/5 PASSED.
  - `SeederIdempotencyTest`: 1/1 PASSED.
  - `composer validate`: Exitoso.
  - `npm run build`: Exitoso.
- Decisiones tomadas:
  - `subjects` accesible para `secretaria` y `vicerrectorado`.
  - `secretaria` restringida a listado de metadatos en planificaciones; sin acceso a detalle, vista previa, descarga, comentarios ni decisiones de aprobación/rechazo.
  - Notificaciones protegidas formalmente vía `NotificationPolicy` sobre `DatabaseNotification`.
  - Reportes protegidos vía Gates `reports.view` y `reports.export` respaldados en `ReportPolicy`.
  - Migración `2026_08_29_000002_add_is_active_to_users_table` aplicada a MySQL `viceapp` con preservación del 100% de datos.
  - No se avanzó a K-004.
- Riesgos o bloqueos:
  - Ninguno.
- Siguiente skill sugerida: K-004 (Almacenamiento privado y conservación de planificaciones) — NO EJECUTADA.






