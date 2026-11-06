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
        'memberships' => 'Forum memberships & campus ambassadors',
        'nominations' => 'Award nominations',
        'awards' => 'Award categories',
        'exhibitions' => 'Exhibition inquiries',
        'sponsorships' => 'Sponsorship enquiries',
        'messages' => 'Contact messages & message log',
        'programmes' => 'Programmes & Grand Summit',
        'agenda' => 'Agenda, venues & halls',
        'speakers' => 'Speakers & experts',
        'news' => 'News',
        'stories' => 'Success stories',
        'partners' => 'Partners & sponsors',
        'exhibitors' => 'Exhibitors & booths',
        'downloads' => 'Downloads',
        'email_templates' => 'Email templates',
        'settings' => 'Settings',
        'users' => 'Users',
        'roles' => 'Roles & permissions',
        'audit_logs' => 'Audit logs',
    ];

    /** Default grants per role (module => verbs); Super Admin needs none. */
    public const ROLES = [
        'Admin' => [
            'pages' => self::VERBS, 'media' => self::VERBS, 'leads' => self::VERBS, 'registrations' => self::VERBS, 'memberships' => self::VERBS,
            'nominations' => self::VERBS, 'awards' => self::VERBS, 'exhibitions' => self::VERBS, 'sponsorships' => self::VERBS, 'messages' => self::VERBS,
            'programmes' => self::VERBS, 'agenda' => self::VERBS, 'speakers' => self::VERBS, 'news' => self::VERBS, 'stories' => self::VERBS,
            'partners' => self::VERBS, 'exhibitors' => self::VERBS, 'downloads' => self::VERBS,
            'email_templates' => self::VERBS, 'settings' => self::VERBS, 'users' => self::VERBS, 'audit_logs' => ['view'],
        ],
        'Manager' => [
            'pages' => self::VERBS, 'media' => self::VERBS, 'leads' => ['view', 'edit'], 'registrations' => ['view', 'edit'], 'memberships' => ['view', 'edit'],
            'nominations' => ['view', 'edit'], 'awards' => self::VERBS, 'exhibitions' => ['view', 'edit'], 'sponsorships' => ['view', 'edit'], 'messages' => ['view', 'edit'],
            'programmes' => self::VERBS, 'agenda' => self::VERBS, 'speakers' => self::VERBS, 'news' => self::VERBS, 'stories' => self::VERBS,
            'partners' => self::VERBS, 'exhibitors' => self::VERBS, 'downloads' => self::VERBS, 'email_templates' => ['view', 'edit'],
        ],
        'Staff' => [
            'pages' => ['view'], 'media' => ['view', 'create'], 'leads' => ['view'], 'registrations' => ['view'], 'memberships' => ['view'], 'nominations' => ['view'],
            'exhibitions' => ['view'], 'sponsorships' => ['view'], 'messages' => ['view'], 'programmes' => ['view'], 'agenda' => ['view'], 'speakers' => ['view'],
            'news' => ['view', 'create', 'edit'], 'stories' => ['view'], 'partners' => ['view'], 'exhibitors' => ['view'], 'downloads' => ['view'],
        ],
    ];

    public static function permission(string $verb, string $module): string
    {
        return "{$verb} {$module}";
    }

    /**
     * Create every permission and the four roles. Existing roles keep the grants an admin set; new roles get the
     * defaults, and a module added after a role was made is granted to it with that role's defaults (once, when the
     * module's permissions are first created).
     */
    public static function sync(): void
    {
        $existing = Permission::query()->where('guard_name', 'web')->pluck('name')->flip();
        $added = array_values(array_filter(array_keys(self::MODULES), fn ($m) => ! isset($existing[self::permission('view', $m)])));
        $fresh = $existing->isEmpty(); // first run: roles below get their full defaults anyway

        foreach (array_keys(self::MODULES) as $module) {
            foreach (self::VERBS as $verb) {
                Permission::findOrCreate(self::permission($verb, $module), 'web');
            }
        }
        Role::findOrCreate(self::SUPER_ADMIN, 'web');
        foreach (self::ROLES as $name => $grants) {
            $role = Role::where('name', $name)->where('guard_name', 'web')->first();
            if (! $role) {
                Role::create(['name' => $name, 'guard_name' => 'web'])->syncPermissions(self::grants($grants));
            } elseif (! $fresh && $added) {
                $role->givePermissionTo(self::grants(array_intersect_key($grants, array_flip($added))));
            }
        }
        // Accounts made before roles existed (or with `make:filament-user`) would otherwise see nothing.
        User::query()->whereDoesntHave('roles')->each(fn (User $u) => $u->assignRole(self::SUPER_ADMIN));
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /** @param array<string, list<string>> $grants module => verbs  @return list<string> permission names */
    private static function grants(array $grants): array
    {
        return collect($grants)->flatMap(fn ($verbs, $module) => array_map(fn ($v) => self::permission($v, $module), $verbs))->values()->all();
    }
}
