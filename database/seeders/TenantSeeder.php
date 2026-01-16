<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;

class TenantSeeder extends Seeder
{
    public function run(): void
    {
        $user1 = User::where('email', 'teste@gmail.com')->first();
        $user2 = User::where('email', 'teste2@gmail.com')->first();
        $user3 = User::where('email', 'teste3@gmail.com')->first();

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
    }
}
