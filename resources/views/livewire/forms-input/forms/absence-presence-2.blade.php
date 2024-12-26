{{-- resources/views/livewire/technicien/partials/inputs/absence-presence-2.blade.php --}}
<div class="input-group input-group-lg">
    {{-- Sélection Présence/Absence --}}
    <select wire:model.live="results.{{ $analyse->id }}.resultats"
            class="form-select">
        <option value="">{{ __('Veuillez choisir') }}</option>
        <option value="Absence" @selected(($results[$analyse->id]['resultats'] ?? '') == 'Absence')>
            {{ __('Absence') }}
        </option>
        <option value="Presence" @selected(($results[$analyse->id]['resultats'] ?? '') == 'Presence')>
            {{ __('Présence de...') }}
        </option>
    </select>

    {{-- Champ de précision si présence --}}
    @if(($results[$analyse->id]['resultats'] ?? '') === 'Presence')
        <input type="text"
               wire:model.live="results.{{ $analyse->id }}.valeur"
               class="form-control"
               placeholder="{{ __('Précisez la présence...') }}"/>
    </select>
    @endif
</div>
