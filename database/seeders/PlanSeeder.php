<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        Plan::create([
            'name' => 'Free',
            'slug' => 'free',
            'price' => 0,
            'limits' => [
                'users' => 5,
                'proposals' => 50,
            ],
            'features' => [
                'Gestão de Clientes',
                'Propostas Básicas',
                'Calendário',
            ],
        ]);

        Plan::create([
            'name' => 'Pro',
            'slug' => 'pro',
            'price' => 49.99,
            'limits' => [
                'users' => 20,
                'proposals' => 'unlimited',
            ],
            'features' => [
                'Tudo do Free',
                'Encomendas Ilimitadas',
                'Faturas',
                'Suporte Prioritário',
            ],
        ]);

        Plan::create([
            'name' => 'Enterprise',
            'slug' => 'enterprise',
            'price' => 149.99,
            'limits' => [
                'users' => 'unlimited',
                'proposals' => 'unlimited',
            ],
            'features' => [
                'Tudo do Pro',
                'Utilizadores Ilimitados',
                'API Access',
                'Suporte Dedicado',
            ],
        ]);
    }
}
