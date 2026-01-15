<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Utilizador 1',
            'email' => 'teste@gmail.com',
            'mobile' => '912345678',
            'password' => bcrypt('andreteste'),
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Utilizador 2',
            'email' => 'teste2@gmail.com',
            'mobile' => '923456789',
            'password' => bcrypt('andreteste'),
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Utilizador 3',
            'email' => 'teste3@gmail.com',
            'mobile' => '934567890',
            'password' => bcrypt('andreteste'),
            'status' => 'active',
            'email_verified_at' => now(),
        ]);
    }
}
