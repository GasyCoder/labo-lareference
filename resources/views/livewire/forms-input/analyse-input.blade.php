{{-- resources/views/livewire/technicien/partials/analyse-input.blade.php --}}
<div class="mb-4">
    <div class="d-flex align-items-center gap-2 mb-3">
        <span @class(['mb-0', 'fw-bold' => $analyse->is_bold])>
            <i class="fas fa-layer-group text-primary me-2"></i>
            @if($analyse->is_bold)
                {{ mb_strtoupper($analyse->designation) }}
            @else
                {{ $analyse->designation }}
            @endif
        </span>
        @if($analyse->is_bold)
            <sup class="badge bg-danger">Important</sup>
        @endif
    </div>

    <div class="rounded bg-light p-3">
        @switch($analyse->analyseType->name)

            @case('INPUT')
            @include('livewire.forms-input.forms.default-input')
                @break

            @case('SELECT')
                @include('livewire.forms-input.forms.select-input')
                @break

            @case('SELECT_MULTIPLE')
                @include('livewire.forms-input.forms.select-multiple-input')
                @break

            @case('DOSAGE')
            @case('COMPTAGE')
                @include('livewire.forms-input.forms.numeric-with-interpretation')
                @break

            @case('ABSENCE_PRESENCE_2')
                @include('livewire.forms-input.forms.absence-presence-2')
                @break

            @case('NEGATIF_POSITIF_1')
                @include('livewire.forms-input.forms.negative-positive-1')
                @break

            @case('NEGATIF_POSITIF_2')
                @include('livewire.forms-input.forms.negative-positive-2')
                @break

            @case('NEGATIF_POSITIF_3')
                @include('livewire.forms-input.forms.negative-positive-3')
                @break

            {{-- Germes forme --}}
            @case('GERME')
                @include('livewire.forms-input.forms.germe-input')
                @break

            @case('LEUCOCYTES')
                @include('livewire.forms-input.forms.leucocytes-input')
                @break

            @case('INPUT_SUFFIXE')
                @include('livewire.forms-input.forms.input-suffixe')
                @break

            @case('LABEL')
                @include('livewire.forms-input.forms.label')
                @break

            @case('FV')
                @include('livewire.forms-input.forms.fv')
                @break

            @case('TEST')
                @include('livewire.forms-input.forms.test')
                @break

            @case('CULTURE')
                @include('livewire.forms-input.forms.culture-input')
                @break

            @default
                {{-- Cas par défaut si nécessaire --}}

        @endswitch

        @include('livewire.forms-input.forms.reference-values')
    </div>
</div>
