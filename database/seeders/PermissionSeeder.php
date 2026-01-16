<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $tenant = Tenant::first();

        if (! $tenant) {
            return;
        }

        config(['app.current_tenant_id' => $tenant->id]);
        setPermissionsTeamId($tenant->id);

        $permissions = [
            'users',
            'roles',
            'entities',
            'contacts',
            'proposals',
            'orders',
            'invoices',
            'calendar',
            'settings',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
                'tenant_id' => $tenant->id,
            ]);
        }

        $superAdmin = Role::firstOrCreate([
            'name' => 'Super Admin',
            'guard_name' => 'web',
            'tenant_id' => $tenant->id,
        ]);
        $superAdmin->syncPermissions($permissions);

        $manager = Role::firstOrCreate([
            'name' => 'Manager',
            'guard_name' => 'web',
            'tenant_id' => $tenant->id,
        ]);
        $manager->syncPermissions([
            'entities',
            'contacts',
            'proposals',
            'orders',
            'invoices',
            'calendar',
        ]);

        $editor = Role::firstOrCreate([
            'name' => 'Editor',
            'guard_name' => 'web',
            'tenant_id' => $tenant->id,
        ]);
        $editor->syncPermissions([
            'entities',
            'contacts',
            'proposals',
            'calendar',
        ]);

        $viewer = Role::firstOrCreate([
            'name' => 'Viewer',
            'guard_name' => 'web',
            'tenant_id' => $tenant->id,
        ]);
        $viewer->syncPermissions([
            'entities',
            'contacts',
            'proposals',
        ]);
    }
}
