<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         User::factory()->count(10)->create();

         // Créer un utilisateur admin avec des identifiants connus
         User::factory()->create([
             'titulaire' => 'Admin User',
             'email' => 'admin@example.com',
             'password' => bcrypt('password'),
             'role' => 'admin'
         ]);

         // Créer un utilisateur client avec des identifiants connus
         User::factory()->create([
             'titulaire' => 'Client User',
             'email' => 'client@example.com',
             'password' => bcrypt('password'),
             'role' => 'client'
         ]);
    }
}


