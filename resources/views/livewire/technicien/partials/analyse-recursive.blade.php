{{-- resources/views/partials/analyse-recursive.blade.php --}}
@foreach($analyses as $analyse)  
    @if($analyse->children->isNotEmpty())
        <h3 class="text-xl font-semibold mt-4 mb-2">{{ $analyse->designation }}</h3>
        @include('livewire.technicien.partials.analyse-recursive', ['analyses' => $analyse->children])
    @else
        @include('livewire.technicien.partials.analyse-input', ['analyse' => $analyse])
    @endif
@endforeach

