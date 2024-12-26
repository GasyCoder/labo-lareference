
{{-- resources/views/livewire/forms/default-input.blade.php --}}
<div class="input-group input-group-lg mb-3">
    <input type="text"
           wire:model="results.{{ $analyse->id }}.valeur"
           class="form-control"
           placeholder="{{ __('Entrez une valeur') }}"
           oninput="this.value = this.value.replace(',', '.')" />

    {{-- Interprétation si applicable --}}
    @if(in_array($analyse->analyseType->name, ['DOSAGE', 'COMPTAGE', 'NEGATIF_POSITIF_1']))
        <select wire:model.live="results.{{ $analyse->id }}.interpretation"
                class="form-select">
            <option value="">---choisir---</option>
            <option value="NORMAL" @selected(($results[$analyse->id]['interpretation'] ?? '') == 'NORMAL')>
                {{ __('NORMAL') }}
            </option>
            <option value="PATHOLOGIQUE" @selected(($results[$analyse->id]['interpretation'] ?? '') == 'PATHOLOGIQUE')>
                {{ __('PATHOLOGIQUE') }}
            </option>
        </select>
    @endif
</div>
{{-- Affichage des unités si disponibles --}}
@if(isset($analyse->result_disponible['unite']))
    <div class="mt-2 small text-muted">
        <i class="fas fa-ruler me-1"></i>
        {{ $analyse->result_disponible['unite'] }}
    </div>
@endif

