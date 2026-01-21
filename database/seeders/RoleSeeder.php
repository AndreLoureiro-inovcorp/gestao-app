<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tenants = Tenant::all();

        foreach ($tenants as $tenant) {
            setPermissionsTeamId($tenant->id);

            $superAdmin = Role::firstOrCreate([
                'name' => 'Super Admin',
                'guard_name' => 'web',
                'tenant_id' => $tenant->id,
            ]);
            $superAdmin->syncPermissions(Permission::all());

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
}
