{{-- resources/views/livewire/technicien/partials/analyse-input.blade.php --}}
<div class="mb-4">
    <span {!! $analyse->is_bold ? 'class="fw-bold"' : '' !!}>
        {{ $analyse->designation }}
    </span>
    <div class="input-group">
        @switch($analyse->analyseType->name)
            @case('SELECT')
            @case('SELECT_MULTIPLE')
            <select
                wire:model="results.{{ $analyse->id }}.valeur"
                class="form-select"
                {{ $analyse->analyseType->name === 'SELECT_MULTIPLE' ? 'multiple' : '' }}>
                    <option value="">Veuillez choisir</option>
                    @foreach($analyse->formatted_results as $value)
                        <option value="{{ $value }}">{{ $value }}</option>
                    @endforeach
            </select>
            @break

            @case('MULTIPLE')
            @case('DOSAGE')
                <input
                    type="text"
                    wire:model="results.{{ $analyse->id }}.interpretation"
                    class="form-control"
                >
                <select wire:model="results.{{ $analyse->id }}.interpretation" class="form-select">
                    <option value="normal">NORMAL</option>
                    <option value="pathologie">PATHOLOGIE</option>
                </select>
                @break

            @case('COMPTAGE')
            @case('INPUT')
                <input
                    type="text"
                    wire:model="results.{{ $analyse->id }}.interpretation"
                    class="form-control"
                    placeholder="Valeur du résultat"
                >
                @break

            @case('INPUT_SUFFIXE')
                <select wire:model="results.bactery.valeur" class="form-select" multiple>
                    <option value="non-rechercher">Non recherché</option>
                    <option value="en-cours">En cours</option>
                    <option value="culture-sterile">Culture stérile</option>
                    <option value="absence">Absence de germe pathogène</option>

                    @foreach($bacteries as $bactery)
                        <optgroup label="{{ $bactery->name }}">
                            @php
                                $bacteriaArray = is_string($bactery->bacteries) ? json_decode($bactery->bacteries) : $bactery->bacteries;
                            @endphp
                            @foreach($bacteriaArray as $bacteri)
                                <option wire:click="bacteries('{{ $bacteri }}')">
                                    {{ $bacteri }}
                                </option>
                            @endforeach
                        </optgroup>
                    @endforeach

                    <option value="autre">Autre</option>
                </select>

                @if($antibiotics_name)
                    <div class="mt-4">
                        <h5 class="mb-3">ANTIBIOGRAMMES</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Antibiotique</th>
                                        <th>Sensibilité</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $antibiotics_data = json_decode($antibiotics_name, true);
                                    @endphp
                                    @foreach($antibiotics_data['antibiotics'] as $antibiotic)
                                        <tr>
                                            <td>{{ $antibiotic }}</td>
                                            <td>
                                                <select class="form-select">
                                                    <option value="">Veuillez choisir</option>
                                                    <option value="resistant">Résistant</option>
                                                    <option value="intermediaire">Intermédiaire</option>
                                                    <option value="sensible">Sensible</option>
                                                </select>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
                @break

            @case('NEGATIF_POSITIF_1')
            @case('NEGATIF_POSITIF_2')
            @case('NEGATIF_POSITIF_3')
                <select wire:model="results.{{ $analyse->id }}.valeur" class="form-select">
                    <option value="">Veuillez choisir</option>
                    <option value="NEGATIF">Négatif</option>
                    <option value="POSITIF">Positif</option>
                </select>
                @if(in_array($analyse->analyseType->name, ['NEGATIF_POSITIF_2', 'NEGATIF_POSITIF_3']))
                    <input
                        type="text"
                        wire:model="results.{{ $analyse->id }}.interpretation"
                        class="form-control"
                        placeholder="Valeur"
                    >
                @endif
                @break

            @case('LEUCOCYTES')
                <input
                    type="number"
                    wire:model="results.{{ $analyse->id }}.valeur"
                    class="form-control"
                    step="1"
                >
                <span class="input-group-text">/mm³</span>
                @break

            @case('FV')
                <select
                    wire:model="results.{{ $analyse->id }}.valeur"
                    class="form-select"
                    {{ $analyse->analyseType->name === 'SELECT_MULTIPLE' ? 'multiple' : '' }}
                >
                    <option value="">Veuillez choisir</option>
                    @foreach($analyse->result_disponible ?? [] as $result)
                        <option value="{{ $result }}">{{ $result }}</option>
                    @endforeach
                </select>
                <input
                    type="number"
                    wire:model="results.{{ $analyse->id }}.valeur"
                    class="form-control"
                    placeholder="Score de Nugent"
                >
                @break

            @default
                <input
                    type="text"
                    wire:model="results.{{ $analyse->id }}.valeur"
                    class="form-control"
                >
        @endswitch
    </div>

    @if(is_array($analyse->result_disponible) &&
        (isset($analyse->result_disponible['val_ref']) ||
         isset($analyse->result_disponible['unite'])))
        <div class="form-text">
            @if(isset($analyse->result_disponible['val_ref']))
                [{{ $analyse->result_disponible['val_ref'] }}]
            @endif
            {{ $analyse->result_disponible['unite'] ?? '' }}
            {{ $analyse->result_disponible['suffixe'] ?? '' }}
        </div>
    @endif

    @if(isset($analyse->description))
        <div class="form-text text-muted">
            {{ $analyse->description }}
        </div>
    @endif
</div>
