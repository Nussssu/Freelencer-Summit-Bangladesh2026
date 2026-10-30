<?php

namespace App\Support;

use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/** CMS roles and permissions: one "{verb} {module}" permission per action and module. Super Admin passes every check. */
class Access
{
    public const SUPER_ADMIN = 'Super Admin';

    public const VERBS = ['view', 'create', 'edit', 'delete'];

    /** module => label */
    public const MODULES = [
        'pages' => 'Pages',
        'media' => 'Media Library',
        'leads' => 'Leads inbox',
        'registrations' => 'Summit registrations',
        'nominations' => 'Award nominations',
        'exhibitions' => 'Exhibition inquiries',
        'email_templates' => 'Email templates',
        'settings' => 'Settings',
        'users' => 'Users',
        'roles' => 'Roles & permissions',
        'audit_logs' => 'Audit logs',
    ];

    /** Default grants per role (module => verbs); Super Admin needs none. */
    public const ROLES = [
        'Admin' => ['pages' => self::VERBS, 'media' => self::VERBS, 'leads' => self::VERBS, 'registrations' => self::VERBS, 'nominations' => self::VERBS,
            'exhibitions' => self::VERBS, 'email_templates' => self::VERBS, 'settings' => self::VERBS, 'users' => self::VERBS, 'audit_logs' => ['view']],
        'Manager' => ['pages' => self::VERBS, 'media' => self::VERBS, 'leads' => ['view', 'edit'], 'registrations' => ['view', 'edit'],
            'nominations' => ['view', 'edit'], 'exhibitions' => ['view', 'edit'], 'email_templates' => ['view', 'edit']],
        'Staff' => ['pages' => ['view'], 'media' => ['view', 'create'], 'leads' => ['view'], 'registrations' => ['view'], 'nominations' => ['view'], 'exhibitions' => ['view']],
    ];

    public static function permission(string $verb, string $module): string
    {
        return "{$verb} {$module}";
    }

    /** Create every permission and the four roles. Existing roles keep the grants an admin set; new ones get the defaults. */
    public static function sync(): void
    {
        foreach (array_keys(self::MODULES) as $module) {
            foreach (self::VERBS as $verb) {
                Permission::findOrCreate(self::permission($verb, $module), 'web');
            }
        }
        Role::findOrCreate(self::SUPER_ADMIN, 'web');
        foreach (self::ROLES as $name => $grants) {
            $role = Role::where('name', $name)->where('guard_name', 'web')->first();
            if (! $role) {
                Role::create(['name' => $name, 'guard_name' => 'web'])->syncPermissions(
                    collect($grants)->flatMap(fn ($verbs, $module) => array_map(fn ($v) => self::permission($v, $module), $verbs))->all()
                );
            }
        }
        // Accounts made before roles existed (or with `make:filament-user`) would otherwise see nothing.
        User::query()->whereDoesntHave('roles')->each(fn (User $u) => $u->assignRole(self::SUPER_ADMIN));
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
