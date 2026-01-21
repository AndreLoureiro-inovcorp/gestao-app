<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            PlanSeeder::class,
            TenantSeeder::class,
            PermissionSeeder::class,
            CountrySeeder::class,
            VatRateSeeder::class,
            RoleSeeder::class,
            AssignRolesSeeder::class,
            BasicDataSeeder::class,
        ]);
    }
}
