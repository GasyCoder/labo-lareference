{{-- resources/views/livewire/technicien/partials/inputs/test.blade.php --}}
<div>
    <select wire:model="results.{{ $analyse->id }}.resultats"
            class="form-select form-select-lg">
        <option value="">{{ __('Veuillez choisir') }}</option>
        <option value="Négatif" @selected(($results[$analyse->id]['resultats'] ?? '') == 'Négatif')>
            {{ __('Négatif') }}
        </option>
        <option value="Positif" @selected(($results[$analyse->id]['resultats'] ?? '') == 'Positif')>
            {{ __('Positif') }}
        </option>
    </select>
</div>
