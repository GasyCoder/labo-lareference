<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $superadmin = Role::create([
            'name' => 'superadmin',
            'label' => 'Super Admin',
            'description' => 'Contrôle total sur tous les aspects du système'
        ]);

        $biologiste = Role::create([
            'name' => 'biologiste',
            'label' => 'Biologiste',
            'description' => 'Gestion des analyses biologiques et validation des résultats'
        ]);

        $secretaire = Role::create([
            'name' => 'secretaire',
            'label' => 'Secrétaire',
            'description' => 'Gestion administrative et accueil des patients'
        ]);

        $technicien = Role::create([
            'name' => 'technicien',
            'label' => 'Technicien',
            'description' => 'Réalisation des analyses en laboratoire'
        ]);

        $superadmin->givePermissionTo([
            'superadmin',
        ]);

        $biologiste->givePermissionTo([
            'biologiste',
        ]);

        $secretaire->givePermissionTo([
            'secretaire',
        ]);

        $technicien->givePermissionTo([
            'technicien',
        ]);

    }
}
