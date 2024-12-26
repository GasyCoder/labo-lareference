{{-- resources/views/livewire/forms-input/forms/select-multiple-input.blade.php --}}
<div class="form-group">
    <select class="form-select select2"
            multiple
            wire:model="results.{{ $analyse->id }}.resultats">
        @foreach($analyse->formatted_results as $value)
            <option value="{{ $value }}"
                    @if(isset($results[$analyse->id]['resultats']) &&
                        is_array($results[$analyse->id]['resultats']) &&
                        in_array($value, $results[$analyse->id]['resultats']))
                        selected
                    @endif>
                {{ $value }}
            </option>
        @endforeach
    </select>
</div>
