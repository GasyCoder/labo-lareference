{{-- resources/views/livewire/forms/negative-positive-2.blade.php --}}
<div class="input-group input-group-lg mb-3">
    <select wire:model.live="results.{{ $analyse->id }}.resultats"
            class="form-select">
        <option value="">{{ __('Veuillez choisir') }}</option>
        <option value="NEGATIF">{{ __('Négatif') }}</option>
        <option value="POSITIF">{{ __('Positif') }}</option>
    </select>

    @if(isset($analyse->result_disponible['val_ref']))
        <div class="mt-2 small text-muted">
            <i class="fas fa-info-circle me-1"></i>
            {{ __('Référence:') }} {{ $analyse->result_disponible['val_ref'] }}
        </div>
    @endif
</div>
