<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;

class AssignRolesSeeder extends Seeder
{
    public function run(): void
    {
        $tenant1 = Tenant::where('slug', 'empresa-1')->first();
        
        if (!$tenant1) {
            return;
        }

        setPermissionsTeamId($tenant1->id);

        $user1 = User::where('email', 'teste@gmail.com')->first();
        if ($user1) {
            $user1->assignRole('Super Admin');
        }
    }
}
