<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AnalyseSqlSeeder extends Seeder
{
    public function run()
    {
        $sqlPath = database_path('seeders/analyses.sql');

        if (!file_exists($sqlPath)) {
            $this->command->error("Le fichier SQL n'existe pas : {$sqlPath}");
            return;
        }

        try {
            // Lecture du fichier SQL
            $sql = file_get_contents($sqlPath);

            // Remplacer les caractères HTML
            $sql = html_entity_decode($sql);
            $sql = str_replace(['&gt;', '&lt;', '&amp;'], ['>', '<', '&'], $sql);

            // Nettoyage des caractères spéciaux
            $sql = preg_replace('/\r\n|\r|\n/', ' ', $sql);

            // Désactivation des contraintes de clés étrangères
            DB::statement('SET FOREIGN_KEY_CHECKS=0');

            // Nettoyage de la table
            DB::statement('TRUNCATE TABLE analyses');

            // Séparation et nettoyage des requêtes INSERT
            preg_match_all('/INSERT INTO `analyses` [^;]+/i', $sql, $matches);

            if (empty($matches[0])) {
                $this->command->error("Aucune instruction INSERT trouvée");
                DB::statement('SET FOREIGN_KEY_CHECKS=1');
                return;
            }

            // Création de la barre de progression
            $totalInserts = count($matches[0]);
            $this->command->info("Début de l'importation de {$totalInserts} enregistrements...");
            $progress = $this->command->getOutput()->createProgressBar($totalInserts);
            $progress->setFormat(' %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s%');
            $progress->start();

            $insertCount = 0;
            foreach ($matches[0] as $insert) {
                try {
                    // Nettoyage supplémentaire et exécution
                    $insert = trim($insert);
                    if (!empty($insert)) {
                        // Ajout du point-virgule si nécessaire
                        if (substr($insert, -1) !== ';') {
                            $insert .= ';';
                        }
                        DB::unprepared($insert);
                        $insertCount++;
                        $progress->advance();
                    }
                } catch (\Exception $e) {
                    $progress->clear();
                    $this->command->error("Erreur lors de l'insertion : " . $e->getMessage());
                    $this->command->error("Requête problématique : " . $insert);
                    $progress->display();
                    continue;
                }
            }

            $progress->finish();
            $this->command->newLine();

            // Vérification du nombre d'enregistrements
            $count = DB::table('analyses')->count();

            // Réactivation des contraintes de clés étrangères
            DB::statement('SET FOREIGN_KEY_CHECKS=1');

            if ($count === 426) {
                $this->command->info("Importation réussie : {$count} enregistrements importés.");
            } else {
                $this->command->error("Nombre d'enregistrements incorrect. Attendu: 426, Obtenu: {$count}");
            }

        } catch (\Exception $e) {
            // Réactivation des contraintes de clés étrangères en cas d'erreur
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            $this->command->error("Erreur : " . $e->getMessage());
        }
    }
}
