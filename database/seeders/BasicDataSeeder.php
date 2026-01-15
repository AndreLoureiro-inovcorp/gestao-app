<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Entity;
use Illuminate\Database\Seeder;

class BasicDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->createDataForTenant(1);
        $this->createDataForTenant(2);
    }

    private function createDataForTenant(int $tenantId): void
    {
        Entity::create([
            'tenant_id' => $tenantId,
            'type' => 'client',
            'number' => 1,
            'tax_number' => "11111111{$tenantId}",
            'name' => "Cliente {$tenantId}A",
            'address' => "Rua Cliente, {$tenantId}0",
            'postal_code' => "{$tenantId}000-001",
            'city' => 'Lisboa',
            'phone' => "21000000{$tenantId}",
            'email' => "cliente{$tenantId}a@exemplo.com",
            'gdpr_consent' => 1,
            'status' => 'active',
        ]);

        Entity::create([
            'tenant_id' => $tenantId,
            'type' => 'client',
            'number' => 2,
            'tax_number' => "22222222{$tenantId}",
            'name' => "Cliente {$tenantId}B",
            'address' => "Rua Cliente, {$tenantId}0",
            'postal_code' => "{$tenantId}000-002",
            'city' => 'Porto',
            'phone' => "22000000{$tenantId}",
            'email' => "cliente{$tenantId}b@exemplo.com",
            'gdpr_consent' => 1,
            'status' => 'active',
        ]);

        Entity::create([
            'tenant_id' => $tenantId,
            'type' => 'supplier',
            'number' => 3,
            'tax_number' => "33333333{$tenantId}",
            'name' => "Fornecedor {$tenantId}A",
            'address' => "Rua Fornecedor, {$tenantId}0",
            'postal_code' => "{$tenantId}000-003",
            'city' => 'Braga',
            'phone' => "25300000{$tenantId}",
            'email' => "fornecedor{$tenantId}a@exemplo.com",
            'gdpr_consent' => 0,
            'status' => 'active',
        ]);

        Entity::create([
            'tenant_id' => $tenantId,
            'type' => 'supplier',
            'number' => 4,
            'tax_number' => "44444444{$tenantId}",
            'name' => "Fornecedor {$tenantId}B",
            'address' => "Rua Fornecedor, {$tenantId}0",
            'postal_code' => "{$tenantId}000-004",
            'city' => 'Coimbra',
            'phone' => "23900000{$tenantId}",
            'email' => "fornecedor{$tenantId}b@exemplo.com",
            'gdpr_consent' => 0,
            'status' => 'active',
        ]);

        Article::create([
            'tenant_id' => $tenantId,
            'number' => "ART{$tenantId}-001",
            'name' => "Produto {$tenantId}A",
            'description' => "Descrição produto {$tenantId}A",
            'price' => 100.00,
            'status' => 'active',
        ]);

        Article::create([
            'tenant_id' => $tenantId,
            'number' => "ART{$tenantId}-002",
            'name' => "Serviço {$tenantId}A",
            'description' => "Descrição serviço {$tenantId}A",
            'price' => 250.00,
            'status' => 'active',
        ]);

        Article::create([
            'tenant_id' => $tenantId,
            'number' => "ART{$tenantId}-003",
            'name' => "Consultoria {$tenantId}",
            'description' => "Descrição consultoria {$tenantId}",
            'price' => 500.00,
            'status' => 'active',
        ]);
    }
}
