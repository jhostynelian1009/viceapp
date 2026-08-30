<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::transaction(function () {
            $mappings = [
                'Docente' => 'docente',
                'Secretaría' => 'secretaria',
                'Secretaria' => 'secretaria',
                'secretaría' => 'secretaria',
                'Vicerrector' => 'vicerrectorado',
                'vicerrector' => 'vicerrectorado',
                'Vicerrectorado' => 'vicerrectorado',
            ];

            $driver = DB::getDriverName();

            foreach ($mappings as $oldName => $canonicalName) {
                if ($oldName === $canonicalName) {
                    continue;
                }

                if (in_array($driver, ['mysql', 'mariadb'])) {
                    $oldRole = DB::table('roles')->whereRaw('BINARY name = ?', [$oldName])->first();
                    $targetRole = DB::table('roles')->whereRaw('BINARY name = ?', [$canonicalName])->first();
                } else {
                    $oldRole = DB::table('roles')->where('name', $oldName)->first();
                    $targetRole = DB::table('roles')->where('name', $canonicalName)->first();
                }

                if (! $oldRole) {
                    continue;
                }

                if (! $targetRole || $oldRole->id === $targetRole->id) {
                    DB::table('roles')->where('id', $oldRole->id)->update([
                        'name' => $canonicalName,
                        'updated_at' => now(),
                    ]);
                } else {
                    $modelRoles = DB::table('model_has_roles')->where('role_id', $oldRole->id)->get();
                    foreach ($modelRoles as $pivot) {
                        $exists = DB::table('model_has_roles')
                            ->where('role_id', $targetRole->id)
                            ->where('model_type', $pivot->model_type)
                            ->where('model_id', $pivot->model_id)
                            ->exists();

                        if (! $exists) {
                            DB::table('model_has_roles')
                                ->where('role_id', $oldRole->id)
                                ->where('model_type', $pivot->model_type)
                                ->where('model_id', $pivot->model_id)
                                ->update(['role_id' => $targetRole->id]);
                        } else {
                            DB::table('model_has_roles')
                                ->where('role_id', $oldRole->id)
                                ->where('model_type', $pivot->model_type)
                                ->where('model_id', $pivot->model_id)
                                ->delete();
                        }
                    }

                    $rolePermissions = DB::table('role_has_permissions')->where('role_id', $oldRole->id)->get();
                    foreach ($rolePermissions as $pivot) {
                        $exists = DB::table('role_has_permissions')
                            ->where('role_id', $targetRole->id)
                            ->where('permission_id', $pivot->permission_id)
                            ->exists();

                        if (! $exists) {
                            DB::table('role_has_permissions')
                                ->where('role_id', $oldRole->id)
                                ->where('permission_id', $pivot->permission_id)
                                ->update(['role_id' => $targetRole->id]);
                        } else {
                            DB::table('role_has_permissions')
                                ->where('role_id', $oldRole->id)
                                ->where('permission_id', $pivot->permission_id)
                                ->delete();
                        }
                    }

                    DB::table('roles')->where('id', $oldRole->id)->delete();
                }
            }
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        throw new \RuntimeException(
            'La migración de normalización de roles realiza una consolidación irreversible de datos históricos. '.
            'Para revertirla se debe restaurar una copia de seguridad previa de la base de datos.'
        );
    }
};
