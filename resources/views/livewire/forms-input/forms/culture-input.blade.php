{{-- resources/views/livewire/forms/culture-input.blade.php --}}
<div class="input-group input-group-lg mb-3">
    <select wire:model.live="results.{{ $analyse->id }}.resultats"
            class="form-select form-select-lg">
        <option value="">{{ __('Veuillez choisir') }}</option>
        <option value="culture-sterile">{{ __('Culture stérile') }}</option>
        <option value="en-cours">{{ __('En cours') }}</option>
        <option value="germe-isole">{{ __('Germe isolé') }}</option>
    </select>

    @if(($results[$analyse->id]['resultats'] ?? '') === 'germe-isole')
        <div class="mt-2">
            <input type="text"
                   wire:model.live="results.{{ $analyse->id }}.valeur"
                   class="form-control form-control-lg"
                   placeholder="{{ __('Précisez le germe isolé...') }}"/>
        </div>
    @endif
</div>
