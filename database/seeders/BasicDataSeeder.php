<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Entity;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class BasicDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tenants = Tenant::all();

        foreach ($tenants as $tenant) {
            $this->createDataForTenant($tenant);
        }
    }

    private function createDataForTenant(Tenant $tenant): void
    {
        Entity::create([
            'tenant_id' => $tenant->id,
            'type' => 'client',
            'number' => 1,
            'tax_number' => '111111111',
            'name' => 'Cliente Demo A',
            'address' => 'Rua Cliente, 10',
            'postal_code' => '1000-001',
            'city' => 'Lisboa',
            'phone' => '210000001',
            'email' => 'clienteA@exemplo.com',
            'gdpr_consent' => 1,
            'status' => 'active',
        ]);

        Entity::create([
            'tenant_id' => $tenant->id,
            'type' => 'client',
            'number' => 2,
            'tax_number' => '222222222',
            'name' => 'Cliente Demo B',
            'address' => 'Rua Cliente, 20',
            'postal_code' => '2000-002',
            'city' => 'Porto',
            'phone' => '220000002',
            'email' => 'clienteB@exemplo.com',
            'gdpr_consent' => 1,
            'status' => 'active',
        ]);

        Entity::create([
            'tenant_id' => $tenant->id,
            'type' => 'supplier',
            'number' => 3,
            'tax_number' => '333333333',
            'name' => 'Fornecedor Demo A',
            'address' => 'Rua Fornecedor, 30',
            'postal_code' => '3000-003',
            'city' => 'Braga',
            'phone' => '253000003',
            'email' => 'fornecedorA@exemplo.com',
            'gdpr_consent' => 0,
            'status' => 'active',
        ]);

        Entity::create([
            'tenant_id' => $tenant->id,
            'type' => 'supplier',
            'number' => 4,
            'tax_number' => '444444444',
            'name' => 'Fornecedor Demo B',
            'address' => 'Rua Fornecedor, 40',
            'postal_code' => '4000-004',
            'city' => 'Coimbra',
            'phone' => '239000004',
            'email' => 'fornecedorB@exemplo.com',
            'gdpr_consent' => 0,
            'status' => 'active',
        ]);

        Article::create([
            'tenant_id' => $tenant->id,
            'number' => 'ART-001',
            'name' => 'Produto Demo',
            'description' => 'Descrição do produto demo',
            'price' => 100.00,
            'status' => 'active',
        ]);

        Article::create([
            'tenant_id' => $tenant->id,
            'number' => 'ART-002',
            'name' => 'Serviço Demo',
            'description' => 'Descrição do serviço demo',
            'price' => 250.00,
            'status' => 'active',
        ]);

        Article::create([
            'tenant_id' => $tenant->id,
            'number' => 'ART-003',
            'name' => 'Consultoria Demo',
            'description' => 'Descrição da consultoria demo',
            'price' => 500.00,
            'status' => 'active',
        ]);
    }
}
