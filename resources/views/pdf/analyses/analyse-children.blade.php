{{-- view/pdf/analyses/children-analyse.blade.php --}}
@foreach($children as $child)
    @php
        $resultat = $child->resultats->first();
        $resultValue = null;
        $jsonString = null;

        // Vérifie si c'est une ligne informative
        $isInfoLine = !$child->result_disponible && $child->designation;

        // Vérifie si le résultat existe et n'est pas vide
        $hasValidResult = false;

        // Ajouter cette nouvelle ligne pour les lignes informatives
        $isInfoLine = !$child->result_disponible && $child->designation && $child->prix == 0;

        if ($resultat) {
            $jsonString = !empty($resultat->valeur) ? $resultat->valeur : $resultat->resultats;

            if ($resultat->valeur !== null) {
                $decodedValue = json_decode($resultat->valeur);
                if ($decodedValue !== null || $decodedValue === "0") {
                    $resultValue = $decodedValue;
                    $hasValidResult = true;
                }
            }
        }

        $selectedOptions = null;
        $antibiogramData = [];
        $bacteriaName = null;
        $type = null;
        $options = null;
        $leucocytesData = null;

        if ($jsonString) {
            if ($child->analyse_type_id === 13) {
                $leucocytesData = json_decode($jsonString, true);
                if (!empty($leucocytesData['valeur'])) {
                    $hasValidResult = true;
                }
            }

            $jsonData = json_decode($jsonString, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($jsonData)) {
                if (isset($jsonData['option_speciale']) && is_array($jsonData['option_speciale'])) {
                    $selectedOptions = implode(', ', array_map('ucfirst', $jsonData['option_speciale']));
                    if (!empty($selectedOptions)) {
                        $hasValidResult = true;
                    }
                }

                if (isset($jsonData['bacteries']) && is_array($jsonData['bacteries'])) {
                    foreach ($jsonData['bacteries'] as $bacterieName => $bacterieData) {
                        $bacteriaName = $bacterieName;
                        if (isset($bacterieData['antibiotics']) && is_array($bacterieData['antibiotics'])) {
                            foreach ($bacterieData['antibiotics'] as $antibiotic => $sensibilite) {
                                $antibiogramData[] = [
                                    'antibiotic' => $antibiotic,
                                    'sensibilite' => is_string($sensibilite) ? ucfirst($sensibilite) : ''
                                ];
                            }
                        }
                    }
                    if (!empty($antibiogramData)) {
                        $hasValidResult = true;
                    }
                }

                $type = $jsonData['typea'] ?? null;
                if ($type) {
                    $hasValidResult = true;
                }
            } else {
                $resultValue = $jsonString;
                if (!empty($resultValue)) {
                    $hasValidResult = true;
                }
            }

            if ($resultat && $resultat->interpretation === 'PATHOLOGIQUE') {
                $resultValue = '<strong>' . ($resultValue ?: 'N/A') . '</strong>';
            }
        }

    // Condition finale pour l'affichage
    $shouldDisplay = $hasValidResult || $isInfoLine;
    @endphp

    @if($shouldDisplay)
        {{-- Partie principale --}}
        @if($bacteriaName)
            {{-- Affichage du Germe isolé --}}
            <tr class="subchild-row">
                <td class="col-designation {{ $child->is_bold ? 'bold' : '' }}">
                    {{ $child->designation }}
                </td>
                <td class="col-resultat">
                    <i>{{ $bacteriaName }}</i>
                </td>
                <td class="col-valref"></td>
                <td class="col-anteriorite"></td>
            </tr>
            {{-- Affichage de l'antibiogramme --}}
            @if(!empty($antibiogramData))
                <tr>
                    <td class="col-designation" style="padding-left: 20px;">
                        Antibiogramme de <i>{{ $bacteriaName }}</i>
                    </td>
                    <td class="col-resultat"></td>
                    <td class="col-valref"></td>
                    <td class="col-anteriorite"></td>
                </tr>
                @foreach($antibiogramData as $data)
                    <tr>
                        <td class="col-designation" style="padding-left: 40px;">
                            {{ $data['antibiotic'] }}
                        </td>
                        <td class="col-resultat">{{ $data['sensibilite'] }}</td>
                        <td class="col-valref"></td>
                        <td class="col-anteriorite"></td>
                    </tr>
                @endforeach
            @endif
        @else
            {{-- Affichage normal pour les autres types d'analyses --}}
            <tr class="subchild-row">
                <td class="col-designation {{ $child->is_bold ? 'bold' : '' }}">
                    {{ $child->designation }}
                </td>
                <td class="col-resultat">
                    @if($child->analyse_type_id === 13 && $leucocytesData)
                        {{ $leucocytesData['valeur'] }} /mm3
                    @else
                        @if($selectedOptions)
                            {!! nl2br(e($selectedOptions)) !!}
                        @endif

                        @if($type)
                            Type : {{ ucfirst($type) }}
                        @endif

                        @if(!$selectedOptions && !$type)
                            @if($resultValue !== null || $resultValue === "0")
                                @if(is_string($resultat->resultats) && in_array($resultat->resultats, [
                                    'Flore vaginale équilibrée',
                                    'Flore vaginale intermédiaire',
                                    'Flore vaginale déséquilibrée'
                                ]))
                                    {!! $resultat->resultats !!}
                                    @if($resultat->valeur)
                                        (Score de Nugent: {!! $resultat->valeur !!})
                                    @endif
                                @else
                                    {!! $resultValue !!}
                                @endif
                            @endif

                            {{-- Garder l'affichage de l'unité --}}
                            @if(isset($child->result_disponible['unite']))
                                {{ $child->result_disponible['unite'] }}
                            @endif

                            {{-- Modification de la condition pour l'affichage de "Nugent" --}}
                            @if($resultat->valeur && in_array($resultat->resultats, [
                                'Flore vaginale équilibrée',
                                'Flore vaginale intermédiaire',
                                'Flore vaginale déséquilibrée'
                            ]))
                                {{-- Ne rien afficher ici car déjà géré au-dessus --}}
                            @endif
                        @endif
                    @endif
                </td>
                <td class="col-valref">
                    @if(is_array($child->result_disponible))
                        {{ $child->result_disponible['val_ref'] ?? '' }}
                    @endif
                </td>
                <td class="col-anteriorite">
                    @if($resultat)
                        {{ $resultat->antecedent }}
                    @endif
                </td>
            </tr>

            {{-- Traitement spécial pour les LEUCOCYTES --}}
            @if($child->analyse_type_id === 13 && $leucocytesData)
                <tr class="subchild-row">
                    <td class="col-designation" style="padding-left: 5em;">Polynucléaires</td>
                    <td class="col-resultat">{{ $leucocytesData['polynucleaires'] }}%</td>
                    <td class="col-valref"></td>
                    <td class="col-anteriorite"></td>
                </tr>
                <tr class="subchild-row">
                    <td class="col-designation" style="padding-left: 5em;">Lymphocytes</td>
                    <td class="col-resultat">{{ $leucocytesData['lymphocytes'] }}%</td>
                    <td class="col-valref"></td>
                    <td class="col-anteriorite"></td>
                </tr>
            @endif
        @endif
    @endif
@endforeach
