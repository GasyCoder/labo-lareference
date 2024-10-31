{{-- resources/views/livewire/technicien/analyse-input.blade.php --}}
<div class="mb-4">
    <h5 @class(['mb-0', 'fw-bold' => $analyse->is_bold])>
        {{ $analyse->designation }}
    </h5>
    <div class="mt-2">
        @switch($analyse->analyseType->name)
            @case('SELECT')
            @case('SELECT_MULTIPLE')
                <select
                    wire:model="results.{{ $analyse->id }}.valeur"
                    class="form-select"
                    {{ $analyse->analyseType->name === 'SELECT_MULTIPLE' ? 'multiple' : '' }}>
                    <option value="">{{ __('Veuillez choisir') }}</option>
                    @foreach($analyse->formatted_results as $value)
                    <option value="{{ $value }}">{{ $value }}</option>
                    @endforeach
                </select>
            @break

            @case('MULTIPLE')
            @case('DOSAGE')
                <div class="input-group">
                    <input
                        type="text"
                        wire:model="results.{{ $analyse->id }}.interpretation"
                        class="form-control"/>
                    <select
                        wire:model="results.{{ $analyse->id }}.interpretation"
                        class="form-select">
                        <option value="normal">{{ __('NORMAL') }}</option>
                        <option value="pathologie">{{ __('PATHOLOGIE') }}</option>
                    </select>
                </div>
            @break

            @case('COMPTAGE')
            @case('INPUT')
                <input
                    type="text"
                    wire:model="results.{{ $analyse->id }}.interpretation"
                    class="form-control"
                    placeholder="{{ __('Valeur du résultat') }}"
                />
                @break

            @case('INPUT_SUFFIXE')
                <div class="mb-3">
                    <select
                        wire:model.live="selectedOption"
                        class="form-select"
                        multiple
                    >
                        <option value="non-rechercher">{{ __('Non recherché') }}</option>
                        <option value="en-cours">{{ __('En cours') }}</option>
                        <option value="culture-sterile">{{ __('Culture stérile') }}</option>
                        <option value="absence">{{ __('Absence de germe pathogène') }}</option>

                        @foreach($bacteries as $bactery)
                            <optgroup label="{{ $bactery->name }}">
                                @foreach(is_string($bactery->bacteries) ? json_decode($bactery->bacteries) : $bactery->bacteries as $bacteri)
                                    <option value="{{ $bacteri }}" wire:click="bacteries('{{ $bacteri }}')">
                                        {{ $bacteri }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endforeach

                        <option value="autre">{{ __('Autre') }}</option>
                    </select>

                    @if($showOtherInput)
                        <div class="mt-3">
                            <input
                                type="text"
                                wire:model="otherBacteriaValue"
                                class="form-control"
                                placeholder="{{ __('Précisez la bactérie') }}"
                            />
                        </div>
                    @endif

                    @if($showAntibiotics && $antibiotics_name)
                        <div class="mt-4">
                            <h5 class="mb-3">{{ __('ANTIBIOGRAMMES') }}</h5>
                            @if($currentBacteria)
                                <div class="alert alert-info mb-3">
                                    {{ __('Bactérie sélectionnée') }}: <strong>{{ $currentBacteria }}</strong>
                                </div>
                            @endif
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>{{ __('Antibiotique') }}</th>
                                            <th>{{ __('Sensibilité') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($antibiotics_name as $antibiotic)
                                            <tr>
                                                <td>{{ $antibiotic }}</td>
                                                <td>
                                                    <select
                                                        wire:model="selectedBacteriaResults.{{ $currentBacteria }}.antibiotics.{{ $antibiotic }}"
                                                        wire:change="updateAntibiogramResult('{{ $antibiotic }}', $event.target.value)"
                                                        class="form-select"
                                                    >
                                                        <option value="">{{ __('Veuillez choisir') }}</option>
                                                        <option value="resistant">{{ __('Résistant') }}</option>
                                                        <option value="intermediaire">{{ __('Intermédiaire') }}</option>
                                                        <option value="sensible">{{ __('Sensible') }}</option>
                                                    </select>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                </div>
                @break

            @case('NEGATIF_POSITIF_1')
            <div class="input-group">
                <input
                    type="text"
                    wire:model="results.{{ $analyse->id }}.interpretation"
                    class="form-control"
                />
                <select
                    wire:model="results.{{ $analyse->id }}.interpretation"
                    class="form-select"
                >
                    <option value="normal">{{ __('NORMAL') }}</option>
                    <option value="pathologie">{{ __('PATHOLOGIE') }}</option>
                </select>
            </div>
            @break
            @case('NEGATIF_POSITIF_2')
                <div>
                    <input
                        type="text"
                        wire:model="results.{{ $analyse->id }}.interpretation"
                        class="form-control"
                        placeholder="{{ __('Valeur') }}"
                    />
                </div>
            @break

            @case('NEGATIF_POSITIF_3')
            <div class="input-group">
                <select
                    wire:model.live="results.{{ $analyse->id }}.valeur"
                    class="form-select">
                    <option value="">{{ __('Veuillez choisir') }}</option>
                    <option value="Absence">{{ __('Absence') }}</option>
                    <option value="Presence">{{ __('Présence de...') }}</option>
                </select>

                @if(($results[$analyse->id]['valeur'] ?? '') === 'Presence')
                <input
                    type="text"
                    wire:model.defer="results.{{ $analyse->id }}.interpretation"
                    class="form-control"
                    placeholder="{{ __('Précisez la présence...') }}"
                />
            @endif
            </div>
            @break

            @case('LEUCOCYTES')
                <div class="input-group">
                    <input
                        type="number"
                        wire:model="results.{{ $analyse->id }}.valeur"
                        class="form-control"
                        step="1"
                    />
                    <span class="input-group-text">/mm³</span>
                </div>
                @break

            @case('FV')
                <div class="input-group">
                    <select
                        wire:model="results.{{ $analyse->id }}.valeur"
                        class="form-select">
                        <option value="">{{ __('Veuillez choisir') }}</option>
                        <option value="FVE">{{ __('Flore vaginale équilibrée') }}</option>
                        <option value="FVI">{{ __('Flore vaginale intermédiaire') }}</option>
                        <option value="FVD">{{ __('Flore vaginale déséquilibrée')}}</option>
                    </select>

                    <input
                        type="number"
                        wire:model="results.{{ $analyse->id }}.valeur"
                        class="form-control"
                        placeholder="{{ __('Score de Nugent') }}"
                    />
                </div>
            @break

            @case('TEST')
            <div>
                <select
                    wire:model="results.{{ $analyse->id }}.valeur"
                    class="form-select mb-2">
                    <option value="">{{ __('Veuillez choisir') }}</option>
                    <option value="NEGATIF">{{ __('Négatif') }}</option>
                    <option value="POSITIF">{{ __('Positif') }}</option>
                </select>
            </div>
            @break

            @default
                <input
                    type="text"
                    wire:model="results.{{ $analyse->id }}.valeur"
                    class="form-control"/>
        @endswitch
    </div>

    {{-- Informations additionnelles --}}
    @if(is_array($analyse->result_disponible) &&
        (isset($analyse->result_disponible['val_ref']) ||
         isset($analyse->result_disponible['unite'])))
        <div class="form-text">
            {{-- @if(isset($analyse->result_disponible['val_ref']))
                <span>[{{ $analyse->result_disponible['val_ref'] }}]</span>
            @endif --}}
            <span>{{ $analyse->result_disponible['unite'] ?? '' }}</span>
            <span>{{ $analyse->result_disponible['suffixe'] ?? '' }}</span>
        </div>
    @endif

    @if(isset($analyse->description))
        <div class="form-text text-muted">
            {{ $analyse->description }}
        </div>
    @endif
</div>
