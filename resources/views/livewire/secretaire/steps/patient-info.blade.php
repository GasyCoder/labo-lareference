{{-- livewire.secretaire.steps.patient-info --}}
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
        {{-- Nom avec suggestions --}}
        <div class="col-md-6" wire:key="nom-field">
            <label for="nom" class="form-label">Nom <span class="text-danger">*</span></label>
            <div class="position-relative">
                <input type="text"
                       id="nom"
                       wire:model.defer="nom"
                       x-on:input="debounceSearch($event)"
                       class="form-control @error('nom') is-invalid @enderror"
                       autocomplete="off">

                @error('nom')<div class="invalid-feedback">{{ $message }}</div>@enderror

                {{-- Liste des suggestions avec optimisation --}}
                @if(!empty($suggestions))
                    <div class="list-group position-absolute w-100 mt-1 shadow-sm"
                         style="z-index: 1000;"
                         wire:key="suggestions-list">
                        @foreach($suggestions as $suggestion)
                            <button type="button"
                                    class="list-group-item list-group-item-action fw-bold"
                                    wire:click="selectSuggestion('{{ $suggestion['nom'] }}', '{{ $suggestion['prenom'] }}')"
                                    wire:loading.attr="disabled">
                                {{ $suggestion['nom'] }} {{ $suggestion['prenom'] }}
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- Prénom --}}
        <div class="col-md-6" wire:key="prenom-field">
            <label for="prenom" class="form-label">Prénom</label>
            <input type="text"
                   id="prenom"
                   wire:model.defer="prenom"
                   class="form-control @error('prenom') is-invalid @enderror">
            @error('prenom')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        {{-- Genre --}}
        <div class="col-md-4" wire:key="sexe-field">
            <label for="sexe" class="form-label">Genre <span class="text-danger">*</span></label>
            <select id="sexe"
                    wire:model.defer="sexe"
                    class="form-select @error('sexe') is-invalid @enderror">
                <option value="">Sélectionnez</option>
                @foreach(['Madame', 'Monsieur', 'Mademoissel', 'Enfant'] as $option)
                    <option value="{{ $option }}">{{ $option }}</option>
                @endforeach
            </select>
            @error('sexe')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        {{-- Âge --}}
        <div class="col-md-4" wire:key="age-field">
            <label for="age" class="form-label">Âge</label>
            <div class="input-group">
                <input type="number"
                       id="age"
                       wire:model.defer="age"
                       class="form-control @error('age') is-invalid @enderror"
                       min="0">
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

        {{-- Poids --}}
        <div class="col-md-4" wire:key="poids-field">
            <label for="poids" class="form-label">Poids (kg)</label>
            <input type="number"
                   step="0.01"
                   id="poids"
                   wire:model.defer="poids"
                   class="form-control @error('poids') is-invalid @enderror"
                   min="0">
            @error('poids')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        {{-- Téléphone --}}
        <div class="col-md-6" wire:key="telephone-field">
            <label for="telephone" class="form-label">Téléphone</label>
            <input type="tel"
                   id="telephone"
                   wire:model.defer="telephone"
                   class="form-control @error('telephone') is-invalid @enderror"
                   pattern="[0-9]*">
            @error('telephone')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        {{-- Email --}}
        <div class="col-md-6" wire:key="email-field">
            <label for="email" class="form-label">Adresse email</label>
            <input type="email"
                   id="email"
                   wire:model.defer="email"
                   class="form-control @error('email') is-invalid @enderror">
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
</div>
