<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $tenant = Tenant::first();

        if (! $tenant) {
            $this->command->warn('Nenhum tenant encontrado! Crie um tenant primeiro.');

            return;
        }

        config(['app.current_tenant_id' => $tenant->id]);

        $permissions = [
            'users.create',
            'users.read',
            'users.update',
            'users.delete',

            'roles.create',
            'roles.read',
            'roles.update',
            'roles.delete',

            'entities.create',
            'entities.read',
            'entities.update',
            'entities.delete',

            'proposals.create',
            'proposals.read',
            'proposals.update',
            'proposals.delete',
        ];

        foreach ($permissions as $permission) {
            Permission::create([
                'name' => $permission,
                'guard_name' => 'web',
                'tenant_id' => $tenant->id,
            ]);
        }

        $superAdmin = Role::create([
            'name' => 'Super Admin',
            'tenant_id' => $tenant->id,
        ]);

        $superAdmin->givePermissionTo(Permission::all());
    }
}
