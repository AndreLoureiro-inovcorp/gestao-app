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
            'name' => 'Administrador',
            'email' => 'teste@gmail.com',
            'mobile' => '912345678',
            'password' => bcrypt('andreteste'),
            'status' => 'active',
            'email_verified_at' => now(),
        ]);
    }
}
