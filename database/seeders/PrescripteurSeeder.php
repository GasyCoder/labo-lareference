<?php
namespace Database\Seeders;

use App\Models\Prescripteur;
use Illuminate\Database\Seeder;

class PrescripteurSeeder extends Seeder
{
   public function run(): void
   {
       $prescripteurs = json_decode(file_get_contents(database_path('seeders/prescripteurs.json')), true);

       $data = array_map(function($nom) {
           return [
               'nom' => trim($nom),
               'is_active' => true,
               'created_at' => now(),
               'updated_at' => now()
           ];
       }, $prescripteurs);

       Prescripteur::insert($data);
   }
}
