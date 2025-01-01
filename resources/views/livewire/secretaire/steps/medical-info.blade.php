{{-- livewire.secretaire.steps.medical-info --}}
<div>
    <h4 class="card-title mb-4">Informations médicales et le prescripteur</h4>

    <div class="row g-3"
         x-data="{
            isSearching: false,
            debounceSearch: function(e) {
                clearTimeout(this.timeout);
                this.timeout = setTimeout(() => {
                    @this.set('prescripteur_search', e.target.value);
                    this.isSearching = true;
                }, 300);
            }
         }"
         x-on:click.away="isSearching = false">

        {{-- Prescripteur --}}
        <div class="col-md-6" wire:key="prescripteur-field">
            <label for="prescripteur_search" class="form-label">
                Nom du prescripteur <span class="text-danger">*</span>
            </label>
            <div class="position-relative">
                <input type="text"
                       id="prescripteur_search"
                       x-on:input="debounceSearch($event)"
                       x-on:focus="isSearching = true"
                       class="form-control @error('prescripteur_search') is-invalid @enderror"
                       autocomplete="off"
                       x-on:change="$wire.nextStep()"
                       x-on:blur="$wire.setNewPrescripteur()"
                       :value="$wire.prescripteur_search">

                {{-- Suggestions et option de création --}}
                <div x-show="isSearching"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 transform scale-95"
                     x-transition:enter-end="opacity-100 transform scale-100"
                     class="position-absolute w-100 bg-white mt-1 rounded shadow-sm"
                     style="z-index: 1000;"
                     wire:key="prescripteur-suggestions">

                    @if(!empty($suggestions))
                        @foreach($suggestions as $prescripteur)
                            <div class="p-2 hover-bg-light cursor-pointer"
                                 wire:click="selectPrescripteur({{ $prescripteur['id'] }}, '{{ $prescripteur['name'] }}')"
                                 wire:loading.class="opacity-50"
                                 wire:target="selectPrescripteur"
                                 role="button"
                                 tabindex="0">
                                {{ $prescripteur['name'] }}
                            </div>
                        @endforeach
                    @endif

                    @if($showCreateOption)
                        <div class="p-2 hover-bg-light cursor-pointer border-top"
                             wire:click="setNewPrescripteur"
                             wire:loading.class="opacity-50"
                             wire:target="setNewPrescripteur"
                             role="button"
                             tabindex="0">
                            <i class="fas fa-plus-circle me-2"></i>
                            Créer "{{ $prescripteur_search }}"
                        </div>
                    @endif

                    <div wire:loading wire:target="prescripteur_search" class="p-2 text-muted">
                        <i class="fas fa-spinner fa-spin me-2"></i>Recherche...
                    </div>
                </div>

                {{-- Messages d'état --}}
                @if($prescripteur_id)
                    <div class="text-success small mt-1" wire:key="selected-prescripteur">
                        <i class="fas fa-check-circle me-1"></i>
                        Prescripteur existant sélectionné : {{ $prescripteur_search }}
                    </div>
                @elseif($nouveau_prescripteur_nom)
                    <div class="text-info small mt-1" wire:key="new-prescripteur">
                        <i class="fas fa-info-circle me-1"></i>
                        Nouveau prescripteur : {{ $nouveau_prescripteur_nom }}
                    </div>
                @endif

                {{-- Messages d'erreur --}}
                @error('prescripteur_search')<div class="invalid-feedback">{{ $message }}</div>@enderror
                @error('prescripteur_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                @error('nouveau_prescripteur_nom')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        {{-- Type de patient --}}
        <div class="col-md-6" wire:key="patient-type-field">
            <label for="patient_type" class="form-label">Type de patient</label>
            <select id="patient_type"
                    wire:model.defer="patient_type"
                    class="form-select @error('patient_type') is-invalid @enderror">
                @foreach(
                        [
                        'EXTERNE' => 'Externe',
                        'HOSPITALISE' => 'Hospitalisé',
                        'URGENCE-NUIT' => 'Urgence-Nuit',//20000Ar
                        'URGENCE-JOUR' => 'Urgence-Jour',//15000Ar
                        ] as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
            @error('patient_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        {{-- Renseignement clinique --}}
        <div class="col-12" wire:key="renseignement-field">
            <label for="renseignement_clinique" class="form-label">Renseignement clinique</label>
            <textarea id="renseignement_clinique"
                      wire:model.defer="renseignement_clinique"
                      rows="3"
                      class="form-control @error('renseignement_clinique') is-invalid @enderror"
                      placeholder="Entrez les renseignements cliniques"></textarea>
            @error('renseignement_clinique')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
</div>
