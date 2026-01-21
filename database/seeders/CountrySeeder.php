<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $countries = [
            ['code' => 'PT', 'name' => 'Portugal'],
            ['code' => 'ES', 'name' => 'Espanha'],
            ['code' => 'FR', 'name' => 'França'],
            ['code' => 'DE', 'name' => 'Alemanha'],
            ['code' => 'IT', 'name' => 'Itália'],
            ['code' => 'GB', 'name' => 'Reino Unido'],
            ['code' => 'NL', 'name' => 'Holanda'],
            ['code' => 'BE', 'name' => 'Bélgica'],
            ['code' => 'CH', 'name' => 'Suíça'],
            ['code' => 'US', 'name' => 'Estados Unidos'],
            ['code' => 'BR', 'name' => 'Brasil'],
            ['code' => 'AO', 'name' => 'Angola'],
            ['code' => 'MZ', 'name' => 'Moçambique'],
        ];

        foreach ($countries as $country) {
            Country::withoutGlobalScopes()->firstOrCreate([
                'code' => $country['code'],
            ], [
                'name' => $country['name'],
            ]);
        }
    }
}
