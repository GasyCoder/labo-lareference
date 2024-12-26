{{-- resources/views/livewire/forms/negative-positive-3.blade.php --}}
<div class="input-group input-group-lg mb-3">
    {{-- Sélection Présence/Absence (Négatif/Positif) --}}
    <select wire:model.live="results.{{ $analyse->id }}.resultats"
            class="form-select">
        <option value="">{{ __('Veuillez choisir') }}</option>
        <option value="NEGATIF">{{ __('Négatif (Absence)') }}</option>
        <option value="POSITIF">{{ __('Positif (Présence)') }}</option>
    </select>
</div>

@if(($results[$analyse->id]['resultats'] ?? '') === 'POSITIF')
    <div class="mb-3">
        {{-- Champ select multiple affiché uniquement si "POSITIF" --}}
        <label class="form-label">{{ __('Éléments détectés :') }}</label>
        <select wire:model.live="results.{{ $analyse->id }}.valeur"
                class="form-select form-select-lg"
                multiple>
            @foreach($analyse->formatted_results as $value)
                <option value="{{ $value }}">{{ $value }}</option>
            @endforeach
        </select>
    </div>
@endif
