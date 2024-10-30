{{-- resources/views/livewire/technicien/partials/analyse-recursive.blade.php --}}
@foreach($analyses as $analyse)
    @if($analyse->children->isNotEmpty())
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">{{ $analyse->designation }}</h5>
            </div>
            <div class="card-body">
                @include('livewire.technicien.partials.analyse-recursive', [
                    'analyses' => $analyse->children
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
