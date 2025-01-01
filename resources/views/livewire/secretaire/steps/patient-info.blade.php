<div>
    <h4 class="card-title mb-4">Informations personnelles du patient</h4>

    <div class="row g-3"
         x-data="{
            hasSuggestions: @entangle('suggestions').defer,
            debounceSearch: function(e) {
                clearTimeout(this.timeout);
                this.timeout = setTimeout(() => {
                    @this.set('nom', e.target.value);
                }, 300);
            }
         }">

        {{-- Champ Nom avec Suggestions --}}
        <div class="col-md-6 position-relative" wire:key="nom-field">
            <label for="nom" class="form-label">Nom <span class="text-danger">*</span></label>
            <div class="position-relative">
                <input type="text"
                       id="nom"
                       wire:model.live="nom"
                       class="form-control @error('nom') is-invalid @enderror"
                       autocomplete="off"
                       placeholder="Entrez le nom du patient"
                       @input="debounceSearch($event)">
                @error('nom')<div class="invalid-feedback">{{ $message }}</div>@enderror

                {{-- Liste des Suggestions --}}
                @if(!empty($suggestions))
                    <div class="dropdown-menu show w-100 mt-1 shadow-lg"
                         style="max-height: 200px; overflow-y: auto; z-index: 1050;">
                        @foreach($suggestions as $suggestion)
                            <button type="button"
                                    class="dropdown-item d-flex justify-content-between align-items-center"
                                    wire:click="selectSuggestion('{{ $suggestion['nom'] }}', '{{ $suggestion['prenom'] }}')"
                                    wire:loading.attr="disabled">
                                <span class="fw-bold">{{ $suggestion['nom'] }}</span>
                                <span class="text-muted">{{ $suggestion['prenom'] }}</span>
                            </button>
                        @endforeach
                    </div>
                @endif
                @if($showCreateOption)
                    <div class="p-2 hover-bg-light cursor-pointer border-top"
                        wire:click="setNewPrescripteur"
                        wire:loading.class="opacity-50"
                        wire:target="setNewPrescripteur"
                        role="button"
                        tabindex="0">
                        <i class="fas fa-plus-circle me-2"></i>
                        Créer "{{ $nom }}"
                    </div>
                @endif
                <div wire:loading wire:target="nom" class="p-2 text-muted">
                    <i class="fas fa-spinner fa-spin me-2"></i>Recherche...
                </div>
            </div>
        </div>

        {{-- Champ Prénom --}}
        <div class="col-md-6" wire:key="prenom-field">
            <label for="prenom" class="form-label">Prénom</label>
            <input type="text"
                   id="prenom"
                   wire:model.defer="prenom"
                   class="form-control @error('prenom') is-invalid @enderror"
                   placeholder="Entrez le prénom">
            @error('prenom')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        {{-- Champ Genre --}}
        <div class="col-md-4" wire:key="sexe-field">
            <label for="sexe" class="form-label">Genre <span class="text-danger">*</span></label>
            <select id="sexe"
                    wire:model.defer="sexe"
                    class="form-select @error('sexe') is-invalid @enderror">
                <option value="">Sélectionnez</option>
                @foreach(['Madame', 'Monsieur', 'Mademoiselle', 'Enfant'] as $option)
                    <option value="{{ $option }}">{{ $option }}</option>
                @endforeach
            </select>
            @error('sexe')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        {{-- Champ Âge --}}
        <div class="col-md-4" wire:key="age-field">
            <label for="age" class="form-label">Âge</label>
            <div class="input-group">
                <input type="number"
                       id="age"
                       wire:model.defer="age"
                       class="form-control @error('age') is-invalid @enderror"
                       min="0"
                       placeholder="Âge">
                <select id="unite_age"
                        wire:model.defer="unite_age"
                        class="form-select @error('unite_age') is-invalid @enderror">
                    @foreach(['Ans', 'Mois', 'Jours'] as $unite)
                        <option value="{{ $unite }}">{{ $unite }}</option>
                    @endforeach
                </select>
                @error('age')<div class="invalid-feedback">{{ $message }}</div>@enderror
                @error('unite_age')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        {{-- Champ Poids --}}
        <div class="col-md-4" wire:key="poids-field">
            <label for="poids" class="form-label">Poids (kg)</label>
            <input type="number"
                   step="0.01"
                   id="poids"
                   wire:model.defer="poids"
                   class="form-control @error('poids') is-invalid @enderror"
                   min="0"
                   placeholder="Poids en kg">
            @error('poids')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        {{-- Champ Téléphone --}}
        <div class="col-md-6" wire:key="telephone-field">
            <label for="telephone" class="form-label">Téléphone</label>
            <input type="tel"
                   id="telephone"
                   wire:model.defer="telephone"
                   class="form-control @error('telephone') is-invalid @enderror"
                   pattern="[0-9]*"
                   placeholder="Numéro de téléphone">
            @error('telephone')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        {{-- Champ Email --}}
        <div class="col-md-6" wire:key="email-field">
            <label for="email" class="form-label">Adresse email</label>
            <input type="email"
                   id="email"
                   wire:model.defer="email"
                   class="form-control @error('email') is-invalid @enderror"
                   placeholder="Adresse email">
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
</div>
