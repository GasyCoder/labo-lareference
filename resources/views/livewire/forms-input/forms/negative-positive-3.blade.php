{{-- resources/views/livewire/forms/negative-positive-3.blade.php --}}
<div class="input-group input-group-lg mb-3">
    <select wire:model.live="results.{{ $analyse->id }}.resultats"
            class="form-select">
        <option value="">{{ __('Veuillez choisir') }}</option>
        <option value="Négatif">{{ __('Négatif (Absence)') }}</option>
        <option value="Positif">{{ __('Positif (Présence)') }}</option>
    </select>
</div>

@if(($results[$analyse->id]['resultats'] ?? '') === 'Positif')
    <div class="mb-3">
        <label class="form-label">{{ __('Éléments détectés :') }}</label>
        <select wire:model.live="results.{{ $analyse->id }}.valeur"
                class="form-select form-select-lg"
                multiple>
            @foreach($analyse->formatted_results as $value)
                <option value="{{ $value }}"
                    {{ isset($results[$analyse->id]['valeur']) &&
                       (is_array($results[$analyse->id]['valeur']) ?
                        in_array($value, $results[$analyse->id]['valeur']) :
                        $results[$analyse->id]['valeur'] === $value) ?
                        'selected' : '' }}>
                    {{ $value }}
                </option>
            @endforeach
        </select>
    </div>
@endif
