{{-- resources/views/livewire/technicien/partials/inputs/select-input.blade.php --}}
<div>
    <select
        wire:model.live="results.{{ $analyse->id }}.resultats"
        class="form-select form-select-lg">
        <option value="">{{ __('Veuillez choisir') }}</option>
        @foreach($analyse->formatted_results as $value)
            @if($value !== 'Autre')
                <option value="{{ $value }}"
                        @selected(($results[$analyse->id]['resultats'] ?? '') == $value)>
                    {{ $value }}
                </option>
            @endif
        @endforeach
        <option value="autre" @selected(($results[$analyse->id]['resultats'] ?? '') == 'autre')>
            {{ __('Autre') }}
        </option>
    </select>

    @if(($results[$analyse->id]['resultats'] ?? '') === 'autre')
        <div class="mt-2">
            <input type="text"
                   wire:model.live="results.{{ $analyse->id }}.valeur"
                   class="form-control form-control-lg"
                   placeholder="{{ __('Précisez votre réponse...') }}"/>
        </div>
    @endif
</div>
