{{-- resources/views/livewire/technicien/partials/inputs/leucocytes-input.blade.php --}}
<div>
    {{-- Groupe Leucocytes --}}
    <div class="mb-3">
        <h6 class="mb-2">Leucocytes (Valeur)</h6>
        <div class="input-group input-group-lg">
            <input type="number"
                   id="leucocytes-valeur"
                   wire:model="results.{{ $analyse->id }}.valeur"
                   class="form-control"
                   step="1"
                   placeholder="Valeur du résultat"/>
            <span class="input-group-text">/mm³</span>
        </div>
    </div>

    {{-- Message d'information après leucocytes --}}
    <div class="alert alert-light mb-3">
        <div class="d-flex align-items-start gap-2">
            <i class="fas fa-info-circle me-2 text-primary mt-1"></i>
            <div class="flex-grow-1">
                {!! nl2br(e($analyse->description)) !!}
            </div>
        </div>
    </div>

    {{-- Groupe Formule leucocytaire --}}
    <div class="ms-4 mt-3">
        <div class="input-group input-group-lg mb-3">
            <span class="input-group-text bg-light">
                <label for="polynucleaire" class="mb-0">Polynucléaire</label>
            </span>
            <input type="number"
                   id="polynucleaire"
                   wire:model="results.{{ $analyse->id }}.polynucleaires"
                   class="form-control"
                   placeholder="{{ __('Valeur du résultat') }}"/>
            <span class="input-group-text">%</span>
        </div>

        <div class="input-group input-group-lg">
            <span class="input-group-text bg-light">
                <label for="lymphocytes" class="mb-0">Lymphocytes</label>
            </span>
            <input type="number"
                   id="lymphocytes"
                   wire:model="results.{{ $analyse->id }}.lymphocytes"
                   class="form-control"
                   placeholder="{{ __('Valeur du résultat') }}"/>
            <span class="input-group-text">%</span>
        </div>
    </div>
</div>
