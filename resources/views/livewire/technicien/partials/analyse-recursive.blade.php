{{-- resources/views/livewire/technicien/partials/analyse-recursive.blade.php --}}
@if($analyses->children->isNotEmpty())
     @foreach($analyses->children as $analyse)
         @if($analyse->children->isNotEmpty())
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 @class(['mb-0', 'fw-bold' => $analyse->is_bold])>
                        {{ mb_strtoupper($analyse->designation) }}
                    </h5>
                </div>
                <div class="card-body">
                    @include('livewire.technicien.partials.analyse-recursive', [
                        'analyses' => $analyse
                    ])
                </div>
            </div>
        @else
            @include('livewire.technicien.partials.analyse-input', [
                'analyse' => $analyse,
                'bacteries' => $bacteries ?? null,
                'antibiotics_name' => $antibiotics_name ?? null
            ])
        @endif
    @endforeach
@else
    @include('livewire.technicien.partials.analyse-input', [
            'analyse' => $analyses,
            'bacteries' => $bacteries ?? null,
            'antibiotics_name' => $antibiotics_name ?? null
    ])
@endif
