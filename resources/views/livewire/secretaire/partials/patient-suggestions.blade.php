<!-- resources/views/livewire/secretaire/partials/patient-suggestions.blade.php -->
@if(!empty($suggestions))
    <div class="list-group mt-2">
        @foreach($suggestions as $suggestion)
            <button type="button" class="list-group-item list-group-item-action fw-bold shadow-2xl"
                wire:click="selectSuggestion('{{ $suggestion['nom'] }}', '{{ $suggestion['prenom'] }}')">
                {{ $suggestion['nom'] }} {{ $suggestion['prenom'] }}
            </button>
        @endforeach
    </div>
@endif
