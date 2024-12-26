{{-- resources/views/livewire/technicien/partials/inputs/numeric-with-interpretation.blade.php --}}
<div class="input-group input-group-lg">
    {{-- Champ valeur --}}
    <input type="text"
        wire:model="results.{{ $analyse->id }}.valeur"
        class="form-control"
        placeholder="{{ __('Entrez une valeur ') }}"
        oninput="this.value = this.value.replace(',', '.')" />
    {{-- Interprétation --}}
    <select wire:model.live="results.{{ $analyse->id }}.interpretation"
            class="form-select">
        <option value="NORMAL" @selected(($results[$analyse->id]['interpretation'] ?? '') == 'NORMAL')>
            {{ __('NORMAL') }}
        </option>
        <option value="PATHOLOGIQUE" @selected(($results[$analyse->id]['interpretation'] ?? '') == 'PATHOLOGIQUE')>
            {{ __('PATHOLOGIQUE') }}
        </option>
    </select>
</div>
