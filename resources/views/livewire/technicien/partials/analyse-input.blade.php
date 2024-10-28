{{-- resources/views/partials/analyse-input.blade.php --}}
<div class="mb-4">
    <label class="block font-semibold mb-1">{{ $analyse->designation }}</label>
    <div class="flex items-center">
        @switch($analyse->analyseType->name)
            @case('SELECT')
            @case('SELECT_MULTIPLE')
            <select wire:model="results.{{ $analyse->id }}.valeur" class="border rounded px-2 py-1 mr-2" {{ $analyse->analyseType->name === 'SELECT_MULTIPLE' ? 'multiple' : '' }}>
                <option value="">Veuillez choisir</option>
                <option value="NEGATIF">Négatif</option>
                <option value="POSITIF">Positif</option>
                <option value="Autre">Autre</option>
            </select>

            @break

            @case('DOSAGE')
            @case('COMPTAGE')
            @case('INPUT')
            @case('INPUT_SUFFIXE')
                <input type="{{ in_array($analyse->analyseType->name, ['DOSAGE', 'COMPTAGE']) ? 'number' : 'text' }}"
                       wire:model="results.{{ $analyse->id }}.valeur"
                       class="border rounded px-2 py-1 mr-2"
                       step="{{ in_array($analyse->analyseType->name, ['DOSAGE', 'COMPTAGE']) ? '0.01' : '1' }}">
                @if($analyse->analyseType->name === 'INPUT_SUFFIXE')
                    <span>{{ $analyse->result_disponible['suffixe'] ?? '' }} </span>
                @endif
            @break
            
            @case('NEGATIF_POSITIF_1')
                <select wire:model="results.{{ $analyse->id }}.valeur" class="border rounded px-2 py-1 mr-2">
                        <option value="">Veuillez choisir</option>
                        <option value="NEGATIF">Négatif</option>
                        <option value="POSITIF">Positif</option>
                </select>
            @break
            @case('NEGATIF_POSITIF_2')
            @case('NEGATIF_POSITIF_3')
                <select wire:model="results.{{ $analyse->id }}.valeur" class="border rounded px-2 py-1 mr-2">
                    <option value="">Veuillez choisir</option>
                    <option value="NEGATIF">Négatif</option>
                    <option value="POSITIF">Positif</option>
                </select>
                @if($analyse->analyseType->name === 'NEGATIF_POSITIF_2')
                    <input type="text" wire:model="results.{{ $analyse->id }}.interpretation"
                           class="border rounded px-2 py-1 mr-2" placeholder="Valeur">
                @elseif($analyse->analyseType->name === 'NEGATIF_POSITIF_3')
                    <select wire:model="results.{{ $analyse->id }}.interpretation" class="border rounded px-2 py-1 mr-2" multiple>
                        @foreach(explode("\n", $analyse->result_disponible) as $option)
                            <option value="{{ trim($option) }}">{{ trim($option) }}</option>
                        @endforeach
                    </select>
                @endif
            @break

            @case('ABSENCE_PRESENCE_2')
                <select wire:model="results.{{ $analyse->id }}.valeur" class="border rounded px-2 py-1 mr-2">
                    <option value="">Veuillez choisir</option>
                    <option value="ABSENCE">Absence</option>
                    <option value="PRESENCE">Présence</option>
                </select>
                <input type="text" wire:model="results.{{ $analyse->id }}.interpretation"
                       class="border rounded px-2 py-1 mr-2" placeholder="Valeur">
            @break

            @case('GERME')
                <select wire:model="results.{{ $analyse->id }}.valeur" class="border rounded px-2 py-1 mr-2">
                    <option value="">Veuillez choisir</option>
                    @foreach(explode("\n", $analyse->result_disponible) as $option)
                        <option value="{{ trim($option) }}">{{ trim($option) }}</option>
                    @endforeach
                </select>
            @break

            @case('LEUCOCYTES')
                <input type="number" wire:model="results.{{ $analyse->id }}.valeur"
                       class="border rounded px-2 py-1 mr-2" step="1">
                <span>/mm³</span>
            @break

            @case('FV')
                <textarea wire:model="results.{{ $analyse->id }}.valeur"
                          class="border rounded px-2 py-1 mr-2" rows="3"></textarea>
            @break

            @default
                <input type="text" wire:model="results.{{ $analyse->id }}.valeur"
                       class="border rounded px-2 py-1 mr-2">
        @endswitch

        @if(is_array($analyse->result_disponible) && (isset($analyse->result_disponible['val_ref']) || isset($analyse->result_disponible['unite'])))
            <span class="text-gray-600">
                [{{ $analyse->result_disponible['val_ref'] ?? '' }}]
                {{ $analyse->result_disponible['unite'] ?? '' }}
            </span>
        @endif
    </div>
</div>
