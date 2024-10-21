<?php

namespace Database\Seeders;

use Carbon\Carbon;
use App\Models\AnalyseType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AnalyseTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $analyseTypes = [
            ['id' => 1, 'name' => 'MULTIPLE', 'libelle' => 'Ensemble de plusieur type elementaire', 'created_at' => '2018-07-25 22:47:23', 'updated_at' => '2018-09-06 20:23:44', 'status' => true],
            ['id' => 2, 'name' => 'TEST', 'libelle' => 'Test', 'created_at' => '2018-07-25 22:50:36', 'updated_at' => '2018-07-25 23:00:28', 'status' => true],
            ['id' => 3, 'name' => 'CULTURE', 'libelle' => 'Culture', 'created_at' => '2018-07-25 22:50:58', 'updated_at' => '2018-07-25 23:00:30', 'status' => true],
            ['id' => 4, 'name' => 'DOSAGE', 'libelle' => 'Dosage', 'created_at' => '2018-08-17 10:43:43', 'updated_at' => '2018-08-17 11:28:50', 'status' => true],
            ['id' => 5, 'name' => 'COMPTAGE', 'libelle' => 'Comptage', 'created_at' => '2018-08-17 11:29:00', 'updated_at' => '2018-09-06 20:20:01', 'status' => true],
            ['id' => 6, 'name' => 'MULTIPLE_SELECTIF', 'libelle' => 'Ensemble de plusieur type elementaire selectif', 'created_at' => '2018-09-05 22:10:05', 'updated_at' => '2018-09-06 20:23:29', 'status' => true],
            ['id' => 7, 'name' => 'INPUT', 'libelle' => 'Champ libre', 'created_at' => '2018-09-06 20:28:45', 'updated_at' => '2018-09-09 21:17:24', 'status' => true],
            ['id' => 8, 'name' => 'SELECT', 'libelle' => 'Champ de selection', 'created_at' => '2018-09-06 20:29:25', 'updated_at' => '2018-09-09 21:17:27', 'status' => true],
            ['id' => 9, 'name' => 'NEGATIF_POSITIF_1', 'libelle' => 'Negatif/Positif', 'created_at' => '2018-09-09 21:20:41', 'updated_at' => '2018-09-09 21:20:59', 'status' => true],
            ['id' => 10, 'name' => 'NEGATIF_POSITIF_2', 'libelle' => 'Negatif/Positif + valeur de ref', 'created_at' => '2018-09-09 21:20:39', 'updated_at' => '2018-09-09 21:21:03', 'status' => true],
            ['id' => 11, 'name' => 'NEGATIF_POSITIF_3', 'libelle' => 'Negatif/Positif + champ select multiple', 'created_at' => '2018-09-09 21:20:37', 'updated_at' => '2018-09-09 21:21:07', 'status' => true],
            ['id' => 12, 'name' => 'INPUT_SUFFIXE', 'libelle' => 'Champ libre + suffixe', 'created_at' => '2018-09-12 22:24:36', 'updated_at' => '2018-09-20 21:19:24', 'status' => true],
            ['id' => 13, 'name' => 'LEUCOCYTES', 'libelle' => 'Leucocytes', 'created_at' => '2018-09-17 20:03:32', 'updated_at' => '2018-10-24 14:04:32', 'status' => true],
            ['id' => 14, 'name' => 'ABSENCE_PRESENCE_2', 'libelle' => 'Absence/Presence + valeur', 'created_at' => '2018-09-20 20:59:55', 'updated_at' => '2018-10-23 18:05:29', 'status' => true],
            ['id' => 15, 'name' => 'GERME', 'libelle' => 'Germe isolé', 'created_at' => '2018-09-24 15:08:26', 'updated_at' => '2018-10-24 14:03:46', 'status' => true],
            ['id' => 16, 'name' => 'LABEL', 'libelle' => 'Simple titre', 'created_at' => '2018-10-23 15:20:05', 'updated_at' => '2018-10-24 14:04:39', 'status' => true],
            ['id' => 17, 'name' => 'SELECT_MULTIPLE', 'libelle' => 'Champ de selection multiple valeur', 'created_at' => '2018-10-23 18:03:25', 'updated_at' => '2018-10-23 18:05:32', 'status' => true],
            ['id' => 18, 'name' => 'FV', 'libelle' => 'Flore vaginale', 'created_at' => '2018-10-24 14:04:09', 'updated_at' => '2018-10-24 14:04:28', 'status' => true],
        ];

        AnalyseType::upsert($analyseTypes, ['id'], ['name', 'libelle', 'status', 'updated_at']);
    }
}
