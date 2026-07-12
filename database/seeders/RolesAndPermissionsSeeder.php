<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * RolesAndPermissionsSeeder — cria roles e permissões básicas via Spatie.
 *
 * Roles: admin, moderator, company_owner, company_recruiter, candidate.
 * Permissões: moderate.posts, moderate.users, manage.jobs,
 *             manage.applications, manage.company, access.admin.
 */
class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Requer tabelas do spatie/laravel-permission
        if (! Schema::hasTable('roles') || ! Schema::hasTable('permissions')) {
            $this->command?->warn('[RolesAndPermissionsSeeder] Tabelas do Spatie não encontradas — pulando.');
            return;
        }

        // Limpa cache do PermissionRegistrar antes de criar/consultar
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $guard = 'web';

        // ---- Permissões -----------------------------------------------------
        $permissions = [
            'moderate.posts',
            'moderate.users',
            'manage.jobs',
            'manage.applications',
            'manage.company',
            'access.admin',
        ];

        foreach ($permissions as $perm) {
            Permission::findOrCreate($perm, $guard);
        }

        // ---- Roles ----------------------------------------------------------
        $roles = [
            'admin'             => $permissions, // todas
            'moderator'         => ['moderate.posts', 'moderate.users'],
            'company_owner'     => ['manage.company', 'manage.jobs', 'manage.applications'],
            'company_recruiter' => ['manage.jobs', 'manage.applications'],
            'candidate'         => [],
        ];

        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::findOrCreate($roleName, $guard);
            $role->syncPermissions($rolePermissions);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->command?->info('[RolesAndPermissionsSeeder] roles e permissões sincronizados.');
    }
}
