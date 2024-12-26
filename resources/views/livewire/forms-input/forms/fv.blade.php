{{-- resources/views/livewire/forms/fv.blade.php --}}
<div class="input-group input-group-lg mb-3">
    <select wire:model="results.{{ $analyse->id }}.resultats" class="form-select">
        <option value="">{{ __('Sélectionnez la flore') }}</option>
        <option value="Flore vaginale équilibrée">{{ __('Flore vaginale équilibrée') }}</option>
        <option value="Flore vaginale intermédiaire">{{ __('Flore vaginale intermédiaire') }}</option>
        <option value="Flore vaginale déséquilibrée">{{ __('Flore vaginale déséquilibrée') }}</option>
    </select>
    {{-- Nugent --}}
    <input type="number"
           wire:model.live="results.{{ $analyse->id }}.valeur"
           class="form-control"
           placeholder="{{ __('Score de Nugent') }}" />
</div>
