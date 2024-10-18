<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use League\Csv\Reader;

class AnalyseCsvSeeder extends Seeder
{
    public function run()
    {
        $csvPath = database_path('seeders/analyses.csv');

        if (!file_exists($csvPath)) {
            $this->command->error("Le fichier CSV n'existe pas : {$csvPath}");
            return;
        }

        $csv = Reader::createFromPath($csvPath, 'r');
        $csv->setDelimiter(',');

        $headers = [
            'code', 'level', 'parent_code', 'abr', 'designation', 'description', 'prix', 'is_bold',
            'examen_id', 'analyse_type_id', 'result_disponible', 'ordre', 'status', 'deleted_at',
            'created_at', 'updated_at'
        ];

        $records = $csv->getRecords($headers);

        DB::beginTransaction();

        try {
            $processedRows = 0;
            $totalRows = iterator_count($records);
            $this->command->info("Début de l'importation de {$totalRows} enregistrements...");

            foreach ($records as $record) {
                $data = [
                    'code' => $record['code'],
                    'level' => $this->validateLevel($record['level']),
                    'parent_code' => $this->nullableValue($record['parent_code']),
                    'abr' => $this->nullableValue($record['abr']),
                    'designation' => $record['designation'],
                    'description' => $this->nullableValue($record['description']),
                    'prix' => $this->nullableValue($record['prix']),
                    'is_bold' => filter_var($record['is_bold'], FILTER_VALIDATE_BOOLEAN),
                    'examen_id' => $this->nullableValue($record['examen_id']),
                    'analyse_type_id' => $this->nullableValue($record['analyse_type_id']),
                    'result_disponible' => $this->validateJson($record['result_disponible']),
                    'ordre' => $this->nullableValue($record['ordre']),
                    'status' => filter_var($record['status'], FILTER_VALIDATE_BOOLEAN),
                    'deleted_at' => $this->nullableValue($record['deleted_at']),
                    'created_at' => $this->nullableValue($record['created_at']),
                    'updated_at' => $this->nullableValue($record['updated_at']),
                ];

                DB::table('analyses')->insert($data);

                $processedRows++;

                if ($processedRows % 100 == 0) {
                    $this->command->info("Traitement en cours : {$processedRows}/{$totalRows} enregistrements");
                }
            }

            DB::commit();
            $this->command->info("Importation terminée. {$processedRows} enregistrements traités avec succès.");
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error("Une erreur est survenue lors de l'importation : " . $e->getMessage());
            $this->command->error("Trace de l'erreur : " . $e->getTraceAsString());
        }
    }

    private function validateJson($value)
    {
        if ($value === 'NULL' || $value === null || $value === '') {
            return null;
        }

        $decoded = json_decode($value, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return json_encode(['value' => str_replace(["\r", "\n"], '', $value)]);
        }

        return $value;
    }

    private function nullableValue($value)
    {
        return ($value === 'NULL' || $value === '') ? null : $value;
    }

    private function validateLevel($level)
    {
        $validLevels = ['PARENT', 'CHILD', 'NORMAL'];
        return in_array(strtoupper($level), $validLevels) ? strtoupper($level) : 'NORMAL';
    }
}
