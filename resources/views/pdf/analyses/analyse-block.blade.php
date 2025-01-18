{{-- analyse-block.blade.php --}}
<div class="analyse-block">
    {{-- Titre de l'analyse --}}
    <div class="analyse-title">
        {{ str_repeat('&nbsp;', ($level * 4)) }}{{ $analyse->designation }}
    </div>

    {{-- Afficher les résultats --}}
    @if($analyse->resultats->isNotEmpty())
        <div class="analyse-results">
            {{ str_repeat('&nbsp;', ($level * 4 + 2)) }}
            {{ $analyse->getFormattedResultValue() }}
        </div>
    @endif

    {{-- Afficher les enfants récursivement --}}
    @if($analyse->children && $analyse->children->count() > 0)
        @foreach($analyse->children as $child)
            @include('pdf.analyses.analyse-block', ['analyse' => $child, 'level' => $level + 1])
        @endforeach
    @endif

    {{-- Description et Conclusion (seulement pour les analyses de niveau parent) --}}
    @if($analyse->level_value === 'PARENT' || !$analyse->parent_code)
        @if(!empty($analyse->description))
            <div class="description-block">
                <div class="block-title">Description :</div>
                <div class="block-content">
                    {!! nl2br(e($analyse->description)) !!}
                </div>
            </div>
        @endif

        @if($analyse->resultats->isNotEmpty() && !empty($analyse->resultats->first()->conclusion))
            <div class="conclusion-block">
                <div class="block-title">Conclusion :</div>
                <div class="block-content">
                    {!! nl2br(e($analyse->resultats->first()->conclusion)) !!}
                </div>
            </div>
        @endif
    @endif
</div>
