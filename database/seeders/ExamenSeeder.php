<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ExamenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $examens = [
            ['id' => 1, 'name' => 'BIOCHIMIE', 'abr' => 'BIO', 'created_at' => '2018-07-25 20:21:56', 'updated_at' => '2018-09-16 15:43:12', 'status' => true],
            ['id' => 2, 'name' => 'HEMATOLOGIE', 'abr' => 'HEM', 'created_at' => '2018-07-25 20:24:50', 'updated_at' => '2019-01-01 20:25:00', 'status' => true],
            ['id' => 3, 'name' => 'PARASITOLOGIE', 'abr' => 'PAR', 'created_at' => '2018-07-25 23:28:39', 'updated_at' => '2019-01-07 08:47:35', 'status' => true],
            ['id' => 4, 'name' => 'SEROLOGIE (TECHNIQUE ELISA, TECHNIQUE IMMUNOCHROMATOGRAPHIQUE)', 'abr' => 'SER', 'created_at' => '2018-09-05 19:58:26', 'updated_at' => '2019-01-19 18:53:03', 'status' => true],
            ['id' => 5, 'name' => 'BACTERIOLOGIE', 'abr' => 'BAC', 'created_at' => '2018-09-13 12:54:48', 'updated_at' => '2019-03-11 03:03:06', 'status' => true],
            ['id' => 6, 'name' => 'IMMUNOLOGIE', 'abr' => 'IMM', 'created_at' => '2018-10-08 17:19:18', 'updated_at' => '2019-01-15 05:41:59', 'status' => true],
            ['id' => 7, 'name' => 'HORMONOLOGIE', 'abr' => 'HOR', 'created_at' => '2018-10-08 18:28:12', 'updated_at' => '2020-01-20 06:37:01', 'status' => true],
            ['id' => 8, 'name' => 'VIROLOGIE', 'abr' => 'VIR', 'created_at' => '2021-01-12 16:13:00', 'updated_at' => '2021-01-12 16:13:46', 'status' => true],
        ];

        foreach ($examens as $examen) {
            DB::table('examens')->insert($examen);
        }
    }
}
