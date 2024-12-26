{{-- view/livewire/pdf.analyses.analyse-row --}}
<tr style="padding-top: 5px;">
    <td class="col-designation {{ ($analyse->level_value === 'PARENT' || $analyse->is_bold) ? 'bold' : '' }}">
        {{ $analyse->designation }}
    </td>
    <td class="col-resultat">
        @if($analyse->resultats->isNotEmpty())
            @php
                $resultat = $analyse->resultats->first();
                $valeur = $resultat->valeur;
                // Décoder la valeur JSON
                $valeurDecodee = json_decode($valeur);
                $isPathologique = $resultat->interpretation === 'PATHOLOGIQUE';
            @endphp
            @if($isPathologique)
                <strong>{{ $valeur }}</strong>
            @else
                {{ $valeur ?? '' }}
            @endif

            @if(isset($analyse->result_disponible['unite']))
                {{ $analyse->result_disponible['unite'] }}
            @endif
        @endif
    </td>
    <td class="col-valref">
        @if(is_array($analyse->result_disponible))
            {{ $analyse->result_disponible['val_ref'] ?? '' }}
        @endif
    </td>
    <td class="col-anteriorite">
        @if($analyse->resultats->isNotEmpty())
            {{ $analyse->resultats->first()->antecedent }}
        @endif
    </td>
</tr>
