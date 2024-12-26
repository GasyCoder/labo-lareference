<?php

namespace App\Services;

class GermeFormatter
{
    public function formatForPdf($resultat): ?string
    {
        if (!$resultat || !$resultat->resultats) {
            return null;
        }

        try {
            $data = is_string($resultat->resultats) ? json_decode($resultat->resultats, true) : $resultat->resultats;

            if (!$data || !isset($data['type']) || $data['type'] !== 'bacterie') {
                return null;
            }

            $output = [];

            // Ajouter le nom de la bactérie
            if (!empty($data['option_speciale'])) {
                $output[] = "Germe isolé";
                $output[] = $data['option_speciale'][0] ?? '';  // Nom de la bactérie
            }

            // Si nous avons des antibiotiques, créer l'antibiogramme
            if (!empty($data['bacteries'])) {
                foreach ($data['bacteries'] as $bacterieName => $bacterieData) {
                    if (!empty($bacterieData['antibiotics'])) {
                        $output[] = "\nAntibiogramme de " . $bacterieName;

                        // Ordonner les antibiotiques dans un ordre spécifique
                        $orderedAntibiotics = [
                            'Amikacine' => null,
                            'Ampicilline / Amoxicilline' => null,
                            'C1G (Cefalotine)' => null,
                            'C4G (céfépime)' => null,
                            'Céfopérazone' => null,
                            'Céfoxitine' => null,
                            'Ciprofloxacine / Ofloxacine' => null,
                            'Gentamicine' => null,
                            'Imipénème' => null,
                            'Levofloxacine' => null,
                            'Phénicolés (Tiamphénicol/Chloramphénicol)' => null,
                            'Triméthoprime sulphaméthoxazole (Bactrim)' => null
                        ];

                        // Remplir les valeurs des antibiotiques
                        foreach ($bacterieData['antibiotics'] as $antibiotic => $sensitivity) {
                            $cleanAntibiotic = $this->cleanAntibioticName($antibiotic);
                            if (array_key_exists($cleanAntibiotic, $orderedAntibiotics)) {
                                $orderedAntibiotics[$cleanAntibiotic] = ucfirst(strtolower($sensitivity));
                            }
                        }

                        // Ajouter les résultats dans l'ordre
                        foreach ($orderedAntibiotics as $antibiotic => $sensitivity) {
                            if ($sensitivity !== null) {
                                $output[] = sprintf("%-45s %s", $antibiotic, $sensitivity);
                            }
                        }
                    }
                }
            }

            return implode("\n", $output);

        } catch (\Exception $e) {
            \Log::error('Erreur formatage germe:', [
                'message' => $e->getMessage(),
                'data' => $resultat->resultats
            ]);
            return null;
        }
    }

    private function cleanAntibioticName($name): string
    {
        $corrections = [
            'C1G (Cefalotine,' => 'C1G (Cefalotine)',
            'Triméthoprime sulphaméthoxazole (Bactrim,' => 'Triméthoprime sulphaméthoxazole (Bactrim)',
            '\/' => '/',
            '\\' => '',
            '"{"":{"":{""}"' => '',
            ',"":""' => '',
            '{"":{""' => '',
            '}}' => ''
        ];

        $name = str_replace(array_keys($corrections), array_values($corrections), $name);
        return trim($name);
    }
}
