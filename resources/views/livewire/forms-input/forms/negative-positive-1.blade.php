<div class="input-group input-group-lg mb-3">
    <select wire:model.live="results.{{ $analyse->id }}.valeur"
            class="form-select">
        <option value="">{{ __('Veuillez choisir') }}</option>
        <option value="Négatif">{{ __('Négatif') }}</option>
        <option value="Positif">{{ __('Positif') }}</option>
    </select>
</div>
