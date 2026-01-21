<?php

namespace Database\Seeders;

use App\Models\Plan;
use App\Models\Tenant;
use App\Models\TenantSubscription;
use App\Models\User;
use Illuminate\Database\Seeder;

class TenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::where('email', 'teste@gmail.com')->first();
        $freePlan = Plan::where('slug', 'free')->first();

        $tenant = Tenant::create([
            'name' => 'Empresa Demo',
            'slug' => 'empresa-demo',
            'owner_id' => $user->id,
            'settings' => [
                'company_name' => 'Empresa Demo, Lda',
                'tax_number' => '123456789',
                'address' => 'Rua Exemplo, 123',
                'postal_code' => '1000-001',
                'city' => 'Lisboa',
            ],
            'status' => 'active',
        ]);

        $tenant->addUser($user, 'owner');

        TenantSubscription::create([
            'tenant_id' => $tenant->id,
            'plan_id' => $freePlan->id,
            'starts_at' => now(),
            'trial_ends_at' => now()->addDays(14),
            'status' => 'active',
        ]);

    }
}
