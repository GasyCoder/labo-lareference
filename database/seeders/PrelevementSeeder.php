<?php

namespace Database\Seeders;

use App\Models\Prelevement;
use Illuminate\Database\Seeder;

class PrelevementSeeder extends Seeder
{
    public function run(): void
    {
        $prelevements = [
            [
                'nom' => 'Prélèvement sang',
                'description' => 'Prélèvement sanguin standard avec matériel stérile',
                'prix' => 4000.00,
                'quantite' => 1, // Ajout de la quantité par défaut
                'is_active' => true
            ],
            [
                'nom' => 'Prélèvement génital',
                'description' => 'Prélèvement pour analyse microbiologique',
                'prix' => 6000.00,
                'quantite' => 1, // Ajout de la quantité par défaut
                'is_active' => true
            ],
            [
                'nom' => 'Tube aiguille',
                'description' => 'Kit complet avec tube et aiguille stérile',
                'prix' => 2000.00,
                'quantite' => 1, // Ajout de la quantité par défaut
                'is_active' => true
            ]
        ];

        foreach ($prelevements as $prelevement) {
            Prelevement::create($prelevement);
        }
    }
}
