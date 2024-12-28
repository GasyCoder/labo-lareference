<h4 class="card-title mb-4">Informations personnelles du patient</h4>
<div class="row g-3">
    <div class="col-md-6">
        <label for="nom" class="form-label">Nom <span class="text-danger">*</span></label>
        <input type="text" id="nom" wire:model.live="nom" class="form-control @error('nom') is-invalid @enderror">
        @error('nom') <div class="text-danger">{{ $message }}</div> @enderror
        @if(!empty($suggestions))
        <div class="list-group mt-2">
            @foreach($suggestions as $suggestion)
                <button type="button" class="list-group-item list-group-item-action fw-bold shadow-2xl"
                    wire:click="selectSuggestion('{{ $suggestion['nom'] }}', '{{ $suggestion['prenom'] }}')">
                    {{ $suggestion['nom'] }} {{ $suggestion['prenom'] }}
                </button>
                @endforeach
            </div>
        @endif
    </div>
    <div class="col-md-6">
        <label for="prenom" class="form-label">Prénom</label>
        <input type="text" id="prenom" wire:model="prenom" class="form-control @error('prenom') is-invalid @enderror">
        @error('prenom') <div class="text-danger">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-4">
        <label for="sexe" class="form-label">Genre <span class="text-danger">*</span></label>
        <select id="sexe" wire:model.defer="sexe" class="form-select @error('sexe') is-invalid @enderror">
            <option value="">Sélectionnez</option>
            <option value="Madame">Madame</option>
            <option value="Monsieur">Monsieur</option>
            <option value="Mademoissel">Mademoiselle</option>
            <option value="Enfant">Enfant</option>
        </select>
        @error('sexe') <div class="text-danger">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-4">
        <label for="age" class="form-label">Âge </label>
        <div class="input-group">
            <input type="number" id="age" wire:model.defer="age" class="form-control @error('age') is-invalid @enderror">
            <select id="unite_age" wire:model.defer="unite_age" class="form-select @error('unite_age') is-invalid @enderror">
                <option value="Ans">Ans</option>
                <option value="Mois">Mois</option>
                <option value="Jours">Jours</option>
            </select>
        </div>
        @error('age') <div class="text-danger">{{ $message }}</div> @enderror
        @error('unite_age') <div class="text-danger">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-4">
        <label for="poids" class="form-label">Poids (kg)</label>
        <input type="number" step="0.01" id="poids" wire:model.defer="poids" class="form-control @error('poids') is-invalid @enderror">
        @error('poids') <div class="text-danger">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label for="telephone" class="form-label">Téléphone</label>
        <input type="text" id="telephone" wire:model="telephone" class="form-control @error('telephone') is-invalid @enderror">
        @error('telephone') <div class="text-danger">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label for="email" class="form-label">Adresse email</label>
        <input type="text" id="email" wire:model="email" class="form-control @error('email') is-invalid @enderror">
        @error('email') <div class="text-danger">{{ $message }}</div> @enderror
    </div>
</div>
