{{-- resources/views/livewire/forms/negative-positive-2.blade.php --}}
<div>
    <div class="input-group input-group-lg mb-3">
        <select wire:model.live="results.{{ $analyse->id }}.resultats"
                class="form-select">
            <option value="">{{ __('Veuillez choisir') }}</option>
            <option value="Négatif">{{ __('Négatif') }}</option>
            <option value="Positif">{{ __('Positif') }}</option>
        </select>
    </div>

    @if(isset($results[$analyse->id]['resultats']) && $results[$analyse->id]['resultats'] === 'Positif')
        <div class="input-group input-group-lg mb-3">
            <input type="text"
                   class="form-control"
                   wire:model="results.{{ $analyse->id }}.valeur"
                   placeholder="Précisez la valeur...">
        </div>
    @endif

    @if(isset($analyse->result_disponible['val_ref']))
        <div class="mt-2 small text-muted">
            <i class="fas fa-info-circle me-1"></i>
            {{ __('Référence:') }} {{ $analyse->result_disponible['val_ref'] }}
        </div>
    @endif
</div>