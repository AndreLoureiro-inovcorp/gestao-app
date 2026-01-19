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
        $user1 = User::where('email', 'teste@gmail.com')->first();
        $user2 = User::where('email', 'teste2@gmail.com')->first();
        $user3 = User::where('email', 'teste3@gmail.com')->first();

        $freePlan = Plan::where('slug', 'free')->first();
        $proPlan = Plan::where('slug', 'pro')->first();

        // Criar Empresa 1
        $tenant1 = Tenant::create([
            'name' => 'Empresa 1',
            'slug' => 'empresa-1',
            'owner_id' => $user1->id,
            'settings' => [
                'company_name' => 'Empresa 1, Lda',
                'tax_number' => '111111111',
                'address' => 'Rua Exemplo, 1',
                'postal_code' => '1000-001',
                'city' => 'Lisboa',
            ],
            'status' => 'active',
        ]);

        $tenant1->addUser($user1, 'owner');
        $tenant1->addUser($user2, 'member');

        // Associar plano Free com trial
        TenantSubscription::create([
            'tenant_id' => $tenant1->id,
            'plan_id' => $freePlan->id,
            'starts_at' => now(),
            'trial_ends_at' => now()->addDays(14),
            'status' => 'active',
        ]);

        // Criar Empresa 2
        $tenant2 = Tenant::create([
            'name' => 'Empresa 2',
            'slug' => 'empresa-2',
            'owner_id' => $user3->id,
            'settings' => [
                'company_name' => 'Empresa 2, Unipessoal',
                'tax_number' => '222222222',
                'address' => 'Avenida Teste, 2',
                'postal_code' => '2000-002',
                'city' => 'Porto',
            ],
            'status' => 'active',
        ]);

        $tenant2->addUser($user3, 'owner');

        // Associar plano Pro sem trial
        TenantSubscription::create([
            'tenant_id' => $tenant2->id,
            'plan_id' => $proPlan->id,
            'starts_at' => now(),
            'status' => 'active',
        ]);
    }
}
