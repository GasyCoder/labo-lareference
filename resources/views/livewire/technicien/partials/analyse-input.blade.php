{{-- resources/views/partials/analyse-input.blade.php --}}
<div class="mb-4">
    <label class="block font-semibold mb-1">{{ $analyse->designation }} </label>
    <div class="flex items-center">
        @switch($analyse->analyseType->name)
            @case('SELECT')
            @case('SELECT_MULTIPLE')
            <select wire:model="results.{{ $analyse->id }}.valeur" class="border rounded px-2 py-1 mr-2" {{ $analyse->analyseType->name === 'SELECT_MULTIPLE' ? 'multiple' : '' }}>
                <option wire:model="results.{{ $analyse->id }}.interpretation">Veuillez choisir</option>
                @if($analyse->result_disponible)
                    @foreach($analyse->result_disponible as $result)
                        <option value="{{$result}}">
                            {{$result}}
                        </option>
                    @endforeach
                @endif
            </select>

            @break
            @case('MULTIPLE')
                <input type="text" wire:model="results.{{ $analyse->id }}.interpretation"
                class="border rounded px-2 py-1 mr-2">
                <select wire:model="results.{{ $analyse->id }}.interpretation" >
                    <option value="normal">NORMAL</option>
                    <option value="pathologie">PATHOLOGIE</option>
                </select>
            @break
            @case('DOSAGE')
                <input type="text" wire:model="results.{{ $analyse->id }}.interpretation"
                    class="border rounded px-2 py-1 mr-2">
                <select wire:model="results.{{ $analyse->id }}.interpretation" >
                    <option value="normal">NORMAL</option>
                    <option value="pathologie">PATHOLOGIE</option>
                </select>
            @break

            @case('COMPTAGE')
            @case('INPUT')
                <input type="text" wire:model="results.{{ $analyse->id }}.interpretation"
                           class="border rounded px-2 py-1 mr-2" placeholder= "Valeur du résultat">
                         
                
            @break
            @case('INPUT_SUFFIXE')
                <select wire:model="results.bactery.valeur"multiple>
                    <option value="non-rechercher" style="font-weight: bold;">Non recherché</option>
                    <option value="en-cours" style="font-weight: bold;">En cours</option>
                    <option value="culture-sterile" style="font-weight: bold;">Culture stérile</option>
                    <option value="absence" style="font-weight: bold;">Absence de germe pathogène</option>
                    @foreach($bacteries as $bactery)
                        <option value="{{$bactery->name}}" style="font-weight: bold;">{{$bactery->name}}</option>
                        @php
                            // Décoder le JSON de la colonne `bacteries` pour obtenir un tableau de bactéries
                            $bacteriaArray = is_string($bactery->bacteries) ? json_decode($bactery->bacteries): $bactery->bacteries;
                        @endphp

                        @if(!empty($bacteriaArray) && is_array($bacteriaArray))
                            <ul>
                                @foreach($bacteriaArray as $bacteri)
                                    <option>{{ $bacteri }}</option> <!-- Affiche chaque bactérie en liste -->
                                @endforeach
                            </ul>
                        @else
                            <p>Aucune bactérie disponible.</p>
                        @endif
                    @endforeach
                    <option value="autre" style="font-weight: bold;">Autre</option>
                </select>
            @break
            
            @case('NEGATIF_POSITIF_1')
                <input type="text" wire:model="results.{{ $analyse->id }}.interpretation"
                            class="border rounded px-2 py-1 mr-2" placeholder= "Valeur du résultat">
                <span class="text-gray-600">
                    {{ $analyse->result_disponible['val_ref'] ?? '' }}
                    {{ $analyse->result_disponible['unite'] ?? '' }}
                    {{ $analyse->result_disponible['suffixe'] ?? '' }}
                </span>
            @break
            @case('NEGATIF_POSITIF_2')
                <input type="text" wire:model="results.{{ $analyse->id }}.interpretation"
                    class="border rounded px-2 py-1 mr-2" placeholder= "Valeur du résultat">
                   
            @break
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
                <select wire:model="results.{{ $analyse->id }}.valeur" class="border rounded px-2 py-1 mr-2" {{ $analyse->analyseType->name === 'SELECT_MULTIPLE' ? 'multiple' : '' }}>
                    <option wire:model="results.{{ $analyse->id }}.interpretation">Veuillez choisir</option>
                    @if($analyse->result_disponible)
                        @foreach($analyse->result_disponible as $result)
                            <option value="{{$result}}">
                                {{$result}}
                            </option>
                        @endforeach
                    @endif
                </select>
                <input type="number" wire:model="results.{{ $analyse->id }}.valeur"
                       class="border rounded px-2 py-1 mr-2" placeholder="Score de Nugent">
            @break

            @case('TEST')
                
            @break

            @default
                <input type="text" wire:model="results.{{ $analyse->id }}.valeur"
                       class="border rounded px-2 py-1 mr-2">
        @endswitch

        @if(is_array($analyse->result_disponible) && (isset($analyse->result_disponible['val_ref']) || isset($analyse->result_disponible['unite'])))
            <span class="text-gray-600">
                [{{ $analyse->result_disponible['val_ref'] ?? '' }}]
                {{ $analyse->result_disponible['unite'] ?? '' }}
                {{ $analyse->result_disponible['suffixe'] ?? '' }}
            </span> <br>
        @endif
        @if(isset($analyse->description))
            <span class="text-gray-600">
                {{ $analyse->description }}
            </span>   
        @endif
    </div>
</div>
