{{-- resources/views/livewire/technicien/analyse-input.blade.php --}}
<div class="mb-4">
    <div class="d-flex align-items-center gap-2 mb-3">
        <h5 @class(['mb-0', 'fw-bold' => $analyse->is_bold])>
            <i class="fas fa-vial text-primary me-2"></i>
            {{ $analyse->designation }}
        </h5>
        @if($analyse->is_bold)
            <span class="badge bg-danger">Important</span>
        @endif
    </div>

    <div class="rounded bg-light p-3">
        @switch($analyse->analyseType->name)
            @case('SELECT')
            @case('SELECT_MULTIPLE')
                <select wire:model="results.{{ $analyse->id }}.valeur"
                        class="form-select form-select-lg"
                        {{ $analyse->analyseType->name === 'SELECT_MULTIPLE' ? 'multiple' : '' }}>
                    <option value="">{{ __('Veuillez choisir') }}</option>
                    @foreach($analyse->formatted_results as $value)
                        <option value="{{ $value }}">{{ $value }}</option>
                    @endforeach
                </select>
                @break

            @case('MULTIPLE')
            @break
            @case('DOSAGE')
                <div class="input-group input-group-lg">
                    <input type="number"
                        wire:model="results.{{ $analyse->id }}.valeur"
                        class="form-control"
                        placeholder="Valeur"/>
                    <select wire:model="results.{{ $analyse->id }}.interpretation"
                            class="form-select" width="20">
                            <option value="">---choisir---</option>
                            <option value="NORMAL">{{ __('NORMAL') }}</option>
                            <option value="PATHOLOGIQUE">{{ __('PATHOLOGIQUE') }}</option>
                    </select>
                </div>
            @break

            @case('COMPTAGE')
                <div class="input-group input-group-lg">
                    <input type="number"
                           wire:model="results.{{ $analyse->id }}.valeur"
                           class="form-control"
                           placeholder="Valeur"/>
                    <select wire:model="results.{{ $analyse->id }}.interpretation"
                            class="form-select">
                            <option value="">---choisir---</option>
                            <option value="NORMAL">{{ __('NORMAL') }}</option>
                            <option value="PATHOLOGIQUE">{{ __('PATHOLOGIQUE') }}</option>
                    </select>
                </div>
            @break
            @case('INPUT')
                <input type="text"
                       wire:model="results.{{ $analyse->id }}.valeur"
                       class="form-control form-control-lg"
                       placeholder="{{ __('Valeur du résultat') }}"/>
                @break

            @case('INPUT_SUFFIXE')
                <div class="mb-3">
                    <select wire:model.live="selectedOption" wire:model="results.{{ $analyse->id }}.valeur"
                            class="form-select form-select-lg mb-3"
                            multiple>
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
                        <input type="text"
                               wire:model="otherBacteriaValue"
                               class="form-control form-control-lg"
                               placeholder="{{ __('Précisez la bactérie') }}"/>
                    @endif

                    @if($showAntibiotics && $antibiotics_name)
                        <div class="mt-4">
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <i class="fas fa-bacteria text-primary"></i>
                                <h5 class="mb-0">{{ __('ANTIBIOGRAMMES') }}</h5>
                            </div>

                            @if($currentBacteria)
                                <div class="alert alert-info d-flex align-items-center gap-2">
                                    <i class="fas fa-info-circle"></i>
                                    <span>{{ __('Bactérie sélectionnée') }}: <strong>{{ $currentBacteria }}</strong></span>
                                </div>
                            @endif

                            <div class="table-responsive">
                                <table class="table table-striped table-hover border">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="fw-semibold">{{ __('Antibiotique') }}</th>
                                            <th class="fw-semibold">{{ __('Sensibilité') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($antibiotics_name as $antibiotic)
                                            <tr>
                                                <td>{{ $antibiotic }}</td>
                                                <td>
                                                    <select wire:model="selectedBacteriaResults.{{ $currentBacteria }}.antibiotics.{{ $antibiotic }}"
                                                            wire:change="updateAntibiogramResult('{{ $antibiotic }}', $event.target.value)"
                                                            wire:model="results.{{ $analyse->id }}.interpretation"
                                                            class="form-select">
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
            <div class="input-group input-group-lg">
                <input
                    type="number"
                    wire:model="results.{{ $analyse->id }}.valeur"
                    class="form-control"/>
                <select
                    wire:model="results.{{ $analyse->id }}.interpretation"
                    class="form-select">
                    <option value="">---choisir---</option>
                    <option value="NORMAL">{{ __('NORMAL') }}</option>
                    <option value="PATHOLOGIQUE">{{ __('PATHOLOGIQUE') }}</option>
                </select>
            </div>
            @break
            @case('NEGATIF_POSITIF_2')
            <div>
                <input
                    type="number"
                    wire:model="results.{{ $analyse->id }}.valeur"
                    class="form-control"
                    placeholder="{{ __('Valeur') }}"
                />
            </div>
            @break

            @case('NEGATIF_POSITIF_3')
                <div class="input-group input-group-lg">
                    <select wire:model.live="results.{{ $analyse->id }}.valeur"
                            class="form-select">
                        <option value="">{{ __('Veuillez choisir') }}</option>
                        <option value="Absence">{{ __('Absence') }}</option>
                        <option value="Presence">{{ __('Présence de...') }}</option>
                    </select>

                    @if(($results[$analyse->id]['valeur'] ?? '') === 'Presence')
                        <input type="text"
                            wire:model.defer="results.{{ $analyse->id }}.valeur"
                            class="form-control"
                            placeholder="{{ __('Précisez la présence...') }}"/>
                    @endif
                </div>
                @break

            @case('LEUCOCYTES')
                <div class="input-group input-group-lg">
                    <input type="number"
                           wire:model="results.{{ $analyse->id }}.valeur"
                           class="form-control"
                           step="1"
                           placeholder="Nombre"/>
                    <span class="input-group-text">/mm³</span>
                </div>
            @break

            @case('LABEL')
            @break

            @case('GERME')
                <div class="mb-3">
                    <select wire:model.live="selectedOption"
                            class="form-select form-select-lg mb-3"
                            multiple>
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
                        <input type="text"
                               wire:model="otherBacteriaValue"
                               class="form-control form-control-lg"
                               placeholder="{{ __('Précisez la bactérie') }}"/>
                    @endif

                    @if($showAntibiotics && $antibiotics_name)
                        <div class="mt-4">
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <i class="fas fa-bacteria text-primary"></i>
                                <h5 class="mb-0">{{ __('ANTIBIOGRAMMES') }}</h5>
                            </div>

                            @if($currentBacteria)
                                <div class="alert alert-info d-flex align-items-center gap-2">
                                    <i class="fas fa-info-circle"></i>
                                    <span>{{ __('Bactérie sélectionnée') }}: <strong>{{ $currentBacteria }}</strong></span>
                                </div>
                            @endif

                            <div class="table-responsive">
                                <table class="table table-striped table-hover border">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="fw-semibold">{{ __('Antibiotique') }}</th>
                                            <th class="fw-semibold">{{ __('Sensibilité') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($antibiotics_name as $antibiotic)
                                            <tr>
                                                <td>{{ $antibiotic }}</td>
                                                <td>
                                                    <select wire:model="selectedBacteriaResults.{{ $currentBacteria }}.antibiotics.{{ $antibiotic }}"
                                                            wire:change="updateAntibiogramResult('{{ $antibiotic }}', $event.target.value)"
                                                            class="form-select">
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
                <div class="input-group input-group-lg">
                    <input type="number"
                        wire:model="results.{{ $analyse->id }}.valeur"
                        class="form-control"
                        placeholder="Valeur"/>
                    <select wire:model="results.{{ $analyse->id }}.interpretation"
                            class="form-select">
                            <option value="">---choisir---</option>
                            <option value="NORMAL">{{ __('NORMAL') }}</option>
                            <option value="PATHOLOGIQUE">{{ __('PATHOLOGIQUE') }}</option>
                    </select>
                </div>
            @break

            @case('NEGATIF_POSITIF_2')
                <div>
                    <input type="text"
                           wire:model="results.{{ $analyse->id }}.valeur"
                           class="form-control form-control-lg"
                           placeholder="{{ __('Valeur') }}"/>
                </div>
                @break

            @case('NEGATIF_POSITIF_3')
                <div class="input-group input-group-lg">
                    <select wire:model.live="results.{{ $analyse->id }}.valeur"
                            class="form-select">
                        <option value="">{{ __('Veuillez choisir') }}</option>
                        <option value="Absence">{{ __('Absence') }}</option>
                        <option value="Presence">{{ __('Présence de...') }}</option>
                    </select>

                    @if(($results[$analyse->id]['valeur'] ?? '') === 'Presence')
                        <input type="text"
                               wire:model.defer="results.{{ $analyse->id }}.ivaleur"
                               class="form-control"
                               placeholder="{{ __('Précisez la présence...') }}"/>
                    @endif
                </div>
                @break

            @case('LEUCOCYTES')
                <div class="input-group input-group-lg">
                    <input type="number"
                           wire:model="results.{{ $analyse->id }}.valeur"
                           class="form-control"
                           step="1"
                           placeholder="Nombre"/>
                    <span class="input-group-text">/mm³</span>
                </div>
                @break

            @case('FV')
                <div class="input-group input-group-lg">
                    <select wire:model="results.{{ $analyse->id }}.valeur"
                            class="form-select">
                        <option value="">{{ __('Veuillez choisir') }}</option>
                        <option value="FVE">{{ __('Flore vaginale équilibrée') }}</option>
                        <option value="FVI">{{ __('Flore vaginale intermédiaire') }}</option>
                        <option value="FVD">{{ __('Flore vaginale déséquilibrée')}}</option>
                    </select>

                    <input type="number"
                           wire:model="results.{{ $analyse->id }}.valeur"
                           class="form-control"
                           placeholder="{{ __('Score de Nugent') }}"/>
                </div>
                @break

            @case('TEST')
                <div>
                    <select wire:model="results.{{ $analyse->id }}.valeur"
                            class="form-select form-select-lg">
                        <option value="">{{ __('Veuillez choisir') }}</option>
                        <option value="NEGATIF">{{ __('Négatif') }}</option>
                        <option value="POSITIF">{{ __('Positif') }}</option>
                    </select>
                </div>
                @break

            @default
                <input type="text"
                       wire:model="results.{{ $analyse->id }}.valeur"
                       class="form-control form-control-lg"/>
        @endswitch

        @if(is_array($analyse->result_disponible) &&
            (!empty($analyse->result_disponible['val_ref']) ||
            !empty($analyse->result_disponible['unite'])))
            <div class="mt-2 small">
                <div class="d-flex align-items-center gap-2 text-muted">
                    <i class="fas fa-info-circle"></i>
                    @if(!empty($analyse->result_disponible['val_ref']))
                        <span class="me-2">Valeurs de référence: [{{ $analyse->result_disponible['val_ref'] }}]</span>
                    @endif
                    @if(!empty($analyse->result_disponible['unite']))
                        <span>{{ $analyse->result_disponible['unite'] }}</span>
                    @endif
                    @if(!empty($analyse->result_disponible['suffixe']))
                        <span>{{ $analyse->result_disponible['suffixe'] }}</span>
                    @endif
                </div>
            </div>
        @endif

        @if(!empty($analyse->description))
            <div class="alert alert-light mt-3 mb-0">
                <i class="fas fa-info-circle me-2 text-primary"></i>
                {{ $analyse->description }}
            </div>
        @endif

    </div>
</div>
