{{-- resources/views/livewire/technicien/partials/inputs/input-suffixe.blade.php --}}
<div class="mb-3">
    <select wire:model.live="selectedOption"
            class="form-select form-select-lg mb-3"
            multiple>
        <option value="non-rechercher" @selected(in_array('non-rechercher', (array)$selectedOption))>
            {{ __('Non recherché') }}
        </option>
        <option value="en-cours" @selected(in_array('en-cours', (array)$selectedOption))>
            {{ __('En cours') }}
        </option>
        <option value="culture-sterile" @selected(in_array('culture-sterile', (array)$selectedOption))>
            {{ __('Culture stérile') }}
        </option>
        <option value="absence" @selected(in_array('absence', (array)$selectedOption))>
            {{ __('Absence de germe pathogène') }}
        </option>

        @foreach($bacteries as $bactery)
            <optgroup label="{{ $bactery->name }}">
                @foreach(is_string($bactery->bacteries) ? json_decode($bactery->bacteries) : $bactery->bacteries as $bacteri)
                    <option value="{{ $bacteri }}"
                            @selected(in_array($bacteri, (array)$selectedOption))
                            wire:click="bacteries('{{ $bacteri }}')">
                        {{ $bacteri }}
                    </option>
                @endforeach
            </optgroup>
        @endforeach

        <option value="autre" @selected(in_array('autre', (array)$selectedOption))>
            {{ __('Autre') }}
        </option>
    </select>

    @if($showOtherInput)
        <input type="text"
            wire:model="otherBacteriaValue"
            class="form-control form-control-lg"
            placeholder="{{ __('Précisez la bactérie') }}"/>
    @endif

    {{-- @if($showAntibiotics && $antibiotics_name)
        @include('livewire.technicien.partials.antibiogram-table')
    @endif --}}
</div>
