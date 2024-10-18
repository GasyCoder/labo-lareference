<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BacteryFamiliesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $families = [
            [
                'id' => 1,
                'name' => 'ENTEROBACTERIACEAE',
                'antibiotics' => json_encode([
                    'Ampicilline / Amoxicilline',
                    'Amoxicilline + acide clavulunique',
                    'C1G (Cefalotine,...)',
                    'C3G (ceftriaxone, céfotaxime, céfixime)',
                    'Céfopérazone',
                    'Phénicolés (Tiamphénicol/Chloramphénicol)',
                    'Ciprofloxacine / Ofloxacine',
                    'Gentamicine',
                    'Imipénème',
                    'Levofloxacine',
                    'Amikacine',
                    'Céfoxitine',
                    'Triméthoprime sulphamétoxazole (Bactrim,...)',
                    'C4G (céfépime)'
                ]),
                'bacteries' => json_encode([
                    'Escherichia coli',
                    'Proteus mirabilis',
                    'Proteus vulgarus',
                    'Klebsiella oxytoca',
                    'Salmonella Typhi',
                    'Shigella sp',
                    'Salmonella sp',
                    'Serratia sp',
                    'Citrobacter sp',
                    'Enterobacter cloacae',
                    'Klebsiella sp',
                    'Enterobacter sp',
                    'Klebsiella pneumoniae',
                    'Entérobactérie'
                ]),
                'status' => true,
                'created_at' => '2018-09-23 20:29:40',
                'updated_at' => '2019-10-25 13:05:55',
            ],
            [
                'id' => 3,
                'name' => 'STAPHYLOCOCCUS',
                'antibiotics' => json_encode([
                    'Penicilline G',
                    'Oxacilline',
                    'Ciprofloxacine / Ofloxacine',
                    'Gentamicine',
                    'Clindamycine',
                    'Thiamphénicol/ chloramphénicol',
                    'Amoxicilline-acide clavulanique',
                    'Érythromycine',
                    'Céfoxitine (Interprétation valable pour Oxacilline)',
                    'Vancomycine',
                    'Rifampicine'
                ]),
                'bacteries' => json_encode([
                    'Staphylococcus aureus',
                    'Staphylococcus à coagulase négative',
                    'autre germe',
                    'S. non aureus',
                    'Staphylococcus sp'
                ]),
                'status' => true,
                'created_at' => '2018-09-23 21:43:55',
                'updated_at' => '2018-09-24 14:01:57',
            ],
            [
                'id' => 4,
                'name' => 'PSEUDOMONAS',
                'antibiotics' => json_encode([
                    'Ticarcilline',
                    'Ceftazidime (Fortum, ...)',
                    'Céfépime',
                    'Imipénème',
                    'Pipéracilline',
                    'Ciprofloxacine',
                    'Lévofloxacine',
                    'Amikacine',
                    'Gentamicine',
                    'Ticarcilline + acide clavulanic (Claventin,...)'
                ]),
                'bacteries' => json_encode([
                    'Pseudomonas aeruginosa',
                    'Stenotrophomonas maltophilia'
                ]),
                'status' => true,
                'created_at' => '2019-02-25 09:00:21',
                'updated_at' => '2019-08-29 07:51:47',
            ],
            [
                'id' => 5,
                'name' => 'Bacilles à Gram positif',
                'antibiotics' => json_encode([
                    'Pénicilline G',
                    'Erythromycine',
                    'Ciprofloxacine',
                    'Gentamicine',
                    'Vancomycine',
                    'Triméthoprime-sulphamétoxazole (Bactrim,...)'
                ]),
                'bacteries' => json_encode([
                    'Bacilles de Doderleïn',
                    'Corynebacterium sp'
                ]),
                'status' => true,
                'created_at' => '2019-05-08 16:24:46',
                'updated_at' => '2019-05-08 16:24:46',
            ],
            [
                'id' => 6,
                'name' => 'Streptococacceae',
                'antibiotics' => json_encode([
                    'Amoxicilline',
                    'Amoxicilline-acide clavulanique',
                    'Pénicilline G',
                    'Ceftriaxone',
                    'Erythromycine',
                    'Triméthoprime sulphamétoxazole (Bactrim,...)',
                    'Chloramphénicol/ Thiamphénicol',
                    'Oxacilline',
                    'Levofloxacine'
                ]),
                'bacteries' => json_encode([
                    'Streptococcus sp.',
                    'Streptococcus pneumoniae',
                    'Streptococcus agalactiae',
                    'Streptococcus pyogenes',
                    'Streptococcus alpha-hémolytique',
                    'Streptococcus beta-hémolytique',
                    'Enterococcus sp'
                ]),
                'status' => true,
                'created_at' => '2019-06-27 14:06:29',
                'updated_at' => '2019-09-09 12:38:48',
            ],
        ];

        foreach ($families as $family) {
            DB::table('bactery_families')->insert([
                'id' => $family['id'],
                'name' => $family['name'],
                'antibiotics' => $family['antibiotics'],
                'bacteries' => $family['bacteries'],
                'status' => $family['status'],
                'created_at' => $family['created_at'],
                'updated_at' => $family['updated_at'],
                'deleted_at' => null,
            ]);
        }
    }
}
