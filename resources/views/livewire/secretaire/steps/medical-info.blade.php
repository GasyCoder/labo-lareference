<h4 class="card-title mb-4">Informations médicales et le prescripteur</h4>
<div class="row g-3">

    <div class="col-md-6">
        <label for="prescripteur_search" class="form-label">Nom du prescripteur <span class="text-danger">*</span></label>
        <div class="position-relative">
            <input type="text" wire:model.live="prescripteur_search" id="prescripteur_search"
            class="form-control" autocomplete="on">
            @if(!empty($suggestions) || $showCreateOption)
                <div class="position-absolute w-100 bg-white mt-1 rounded shadow-sm" style="z-index: 1000;">
                    @foreach($suggestions as $prescripteur)
                        <div class="p-2 hover-bg-light cursor-pointer" wire:click="selectPrescripteur({{ $prescripteur['id'] ?? '' }}, '{{ $prescripteur['name'] }}')">
                            {{ $prescripteur['name'] }}
                        </div>
                    @endforeach
                    @if($showCreateOption)
                        <div class="p-2 hover-bg-light cursor-pointer shadow-xl fw-bold" wire:click="setNewPrescripteur">
                            Créer "{{ $prescripteur_search }}"
                        </div>
                    @endif
                </div>
            @endif
        </div>
        @if($prescripteur_id)
            <div class="text-success small mt-1">Prescripteur existant sélectionné : {{ $prescripteur_search }}</div>
        @elseif($nouveau_prescripteur_nom)
            <div class="text-info small mt-1">Prescripteur à créer : {{ $nouveau_prescripteur_nom }}</div>
        @endif
        @error('prescripteur_search') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
        @error('prescripteur_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
        @error('nouveau_prescripteur_nom') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label for="patient_type" class="form-label">Type de patient</label>
        <select id="patient_type" wire:model.defer="patient_type" class="form-select @error('patient_type') is-invalid @enderror">
            <option value="EXTERNE">Externe</option>
            <option value="HOSPITALISE">Hospitalisé</option>
        </select>
        @error('patient_type') <div class="text-danger">{{ $message }}</div> @enderror
    </div>

    <div class="col-12">
        <label for="renseignement_clinique" class="form-label">Renseignement clinique</label>
        <textarea id="renseignement_clinique" wire:model.defer="renseignement_clinique" rows="3" class="form-control @error('renseignement_clinique') is-invalid @enderror"></textarea>
        @error('renseignement_clinique') <div class="text-danger">{{ $message }}</div> @enderror
    </div>
</div>
