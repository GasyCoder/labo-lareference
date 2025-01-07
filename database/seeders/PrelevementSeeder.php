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
                'quantite' => 1,
                'is_active' => true
            ],
            [
                'nom' => 'Prélèvement génital',
                'description' => 'Prélèvement pour analyse microbiologique',
                'prix' => 6000.00,
                'quantite' => 1,
                'is_active' => true
            ],
            [
                'nom' => 'Tube aiguille',
                'description' => 'Kit complet avec tube et aiguille stérile',
                'prix' => 2000.00,
                'quantite' => 1,
                'is_active' => true
            ],
            [
                'nom' => 'Flacon stérile',
                'description' => 'Flacon stérile pour prélèvement',
                'prix' => 2000.00,
                'quantite' => 1,
                'is_active' => true
            ],
            [
                'nom' => 'Flacon non stérile',
                'description' => 'Flacon non stérile pour prélèvement',
                'prix' => 1200.00,
                'quantite' => 1,
                'is_active' => true
            ],
            [
                'nom' => 'Collecteur d\'urines',
                'description' => 'Un flacon non stérile conçu pour le prélèvement d\'échantillons d\'urine.',
                'prix' => 2000.00,
                'quantite' => 1,
                'is_active' => true
            ]
        ];

        foreach ($prelevements as $prelevement) {
            Prelevement::updateOrCreate(
                ['nom' => $prelevement['nom']], // Condition : éviter les doublons basés sur le nom
                $prelevement // Données à insérer ou mettre à jour
            );
        }
    }
}
