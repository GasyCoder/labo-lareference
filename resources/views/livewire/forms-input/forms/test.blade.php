{{-- resources/views/livewire/technicien/partials/inputs/test.blade.php --}}
<div>
    <select wire:model="results.{{ $analyse->id }}.resultats"
            class="form-select form-select-lg">
        <option value="">{{ __('Veuillez choisir') }}</option>
        <option value="NEGATIF" @selected(($results[$analyse->id]['resultats'] ?? '') == 'NEGATIF')>
            {{ __('Négatif') }}
        </option>
        <option value="POSITIF" @selected(($results[$analyse->id]['resultats'] ?? '') == 'POSITIF')>
            {{ __('Positif') }}
        </option>
    </select>
</div>
