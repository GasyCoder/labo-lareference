{{-- resources/views/livewire/admin/analyses/analyse-hierarchy.blade.php --}}
<ul class="list-unstyled ms-{{ $level * 3 }}">
    @foreach($analyses as $item)
        <li class="mb-2">
            <div class="card mb-2">
                <div class="card-body">
                    <h6 class="card-title">
                        <i class="fas fa-{{ $item['analyse']->level === \App\Enums\AnalyseLevel::PARENT ? 'folder' : ($item['analyse']->level === \App\Enums\AnalyseLevel::CHILD ? 'file-alt' : 'vial') }} me-2"></i>
                        {{ $item['analyse']->designation }}
                        <span class="badge bg-secondary ms-2">{{ $item['analyse']->level }}</span>
                    </h6>
                    <p class="card-text">
                        <small class="text-muted">Code: {{ $item['analyse']->abr }}</small>
                    </p>
                    <p class="card-text">
                        <small class="text-muted">Ordre: {{ $item['analyse']->ordre ?? 0 }}</small>
                    </p>
                    <button class="btn btn-sm btn-outline-primary" wire:click="viewDetails({{ $item['analyse']->id }})">
                        <i class="fas fa-info-circle me-1"></i>Détails
                    </button>
                </div>
            </div>
            @if(count($item['children']) > 0)
                @include('livewire.admin.analyses.analyse-hierarchy', ['analyses' => $item['children'], 'level' => $level + 1])
            @endif
        </li>
    @endforeach
</ul>
