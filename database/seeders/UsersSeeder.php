<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Création du Super Admin
        $superadmin = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@labo.com',
            'password' => Hash::make('superadmin@123'),
            'email_verified_at' => now(),
        ]);
        $superadmin->assignRole('superadmin');

        // Création du Biologiste
        $biologiste = User::create([
            'name' => 'Biologiste Principal',
            'email' => 'biologiste@labo.com',
            'password' => Hash::make('biologiste123'),
            'email_verified_at' => now(),
        ]);
        $biologiste->assignRole('biologiste');

        // Création de la Secrétaire
        $secretaire = User::create([
            'name' => 'Secrétaire Médicale',
            'email' => 'secretaire@labo.com',
            'password' => Hash::make('secretaire123'),
            'email_verified_at' => now(),
        ]);
        $secretaire->assignRole('secretaire');

        // Création du Technicien
        $technicien = User::create([
            'name' => 'Technicien de Laboratoire',
            'email' => 'technicien@labo.com',
            'password' => Hash::make('technicien123'),
            'email_verified_at' => now(),
        ]);
        $technicien->assignRole('technicien');
    }
}
