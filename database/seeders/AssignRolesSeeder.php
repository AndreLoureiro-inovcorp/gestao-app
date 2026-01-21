<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;

class AssignRolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tenant = Tenant::where('slug', 'empresa-demo')->first();

        if (! $tenant) {
            return;
        }

        setPermissionsTeamId($tenant->id);

        $user = User::where('email', 'teste@gmail.com')->first();
        if ($user) {
            $user->assignRole('Super Admin');
        }
    }
}
