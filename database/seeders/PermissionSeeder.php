<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run()
    {
        $permissions = [
            'superadmin',
            'biologiste',
            'secretaire',
            'technicien',
        ];

        $label_permissions = [
            'Super Admin',
            'Biologiste',
            'Secretaire',
            'Technicien',
        ];

        $descriptions_permissions = [
            'Toutes les permissions du système',
            'Permissions spécifiques au biologiste',
            'Permissions spécifiques au secrétaire',
            'Permissions spécifiques au technicien',
        ];

        // Loop through the permissions and create them using Spatie
        foreach ($permissions as $key => $permissionName) {
            Permission::create([
                'name' => $permissionName,
                'label' => $label_permissions[$key],
                'description' => $descriptions_permissions[$key],
            ]);
        }
    }
}
