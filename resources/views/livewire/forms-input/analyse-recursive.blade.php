{{-- resources/views/livewire/forms-input/analyse-recursive.blade.php --}}
<div class="analyse-wrapper">
    @if($analyses->children->isNotEmpty())
        @foreach($analyses->children as $analyse)
            @if($analyse->children->isNotEmpty())
                {{-- Groupe d'analyses avec enfants --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-light py-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <h5 class="mb-0 @if($analyse->is_bold) fw-bold @endif">
                                <i class="fas fa-layer-group text-primary me-2"></i>
                                {{ mb_strtoupper($analyse->designation) }}
                            </h5>
                            @if($analyse->is_bold)
                                <span class="badge bg-danger">Important</span>
                            @endif
                        </div>
                    </div>

                    <div class="card-body p-4">
                        <div class="ps-3 border-start">
                            @include('livewire.forms-input.analyse-recursive', [
                                'analyses' => $analyse
                            ])
                        </div>
                    </div>
                </div>
            @else
                {{-- Analyse individuelle --}}
                <div class="ps-3 border-start mb-4">
                    @include('livewire.forms-input.analyse-input', [
                        'analyse' => $analyse,
                        'bacteries' => $bacteries ?? null,
                        'antibiotics_name' => $antibiotics_name ?? null
                    ])
                </div>
            @endif
        @endforeach
    @else
        {{-- Analyse simple sans enfants --}}
        <div class="mb-4">
            @include('livewire.forms-input.analyse-input', [
                'analyse' => $analyses,
                'bacteries' => $bacteries ?? null,
                'antibiotics_name' => $antibiotics_name ?? null
            ])
        </div>
    @endif
</div>
