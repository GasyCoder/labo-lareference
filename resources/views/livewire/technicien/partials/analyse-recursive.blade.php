@if($analyses->children->isNotEmpty())
    @foreach($analyses->children as $analyse)
        @if($analyse->children->isNotEmpty())
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light py-3">
                    <h5 @class(['mb-0', 'fw-bold' => $analyse->is_bold])>
                        <i class="fas fa-layer-group me-2"></i>
                        {{ mb_strtoupper($analyse->designation) }}
                    </h5>
                </div>
                <div class="card-body p-4">
                    @include('livewire.technicien.partials.analyse-recursive', [
                        'analyses' => $analyse
                    ])
                </div>
            </div>
        @else
            <div class="mb-4">
                @include('livewire.technicien.partials.analyse-input', [
                    'analyse' => $analyse,
                    'bacteries' => $bacteries ?? null,
                    'antibiotics_name' => $antibiotics_name ?? null
                ])
            </div>
        @endif
    @endforeach
@else
    <div class="mb-4">
        @include('livewire.technicien.partials.analyse-input', [
            'analyse' => $analyses,
            'bacteries' => $bacteries ?? null,
            'antibiotics_name' => $antibiotics_name ?? null
        ])
    </div>
@endif
