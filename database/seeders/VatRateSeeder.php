<?php

namespace Database\Seeders;

use App\Models\VatRate;
use Illuminate\Database\Seeder;

class VatRateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vatRates = [
            [
                'name' => 'Normal',
                'rate' => 23.00,
                'description' => 'Taxa Normal de IVA (23%)',
                'is_default' => true,
            ],
            [
                'name' => 'Intermediária',
                'rate' => 13.00,
                'description' => 'Taxa Intermediária de IVA (13%)',
                'is_default' => false,
            ],
            [
                'name' => 'Reduzida',
                'rate' => 6.00,
                'description' => 'Taxa Reduzida de IVA (6%)',
                'is_default' => false,
            ],
            [
                'name' => 'Isento',
                'rate' => 0.00,
                'description' => 'Isento de IVA (0%)',
                'is_default' => false,
            ],
        ];

        foreach ($vatRates as $vat) {
            VatRate::withoutGlobalScopes()->firstOrCreate([
                'name' => $vat['name'],
            ], [
                'rate' => $vat['rate'],
                'description' => $vat['description'],
                'is_default' => $vat['is_default'],
            ]);
        }
    }
}
