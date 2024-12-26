{{-- resources/views/livewire/technicien/partials/reference-values.blade.php --}}

{{-- resources/views/livewire/technicien/partials/reference-values.blade.php --}}
@if(is_array($analyse->result_disponible) &&
    (!empty($analyse->result_disponible['val_ref']) ||
     !empty($analyse->result_disponible['unite']) ||
     !empty($analyse->result_disponible['suffixe'])))
    <div class="mt-2 small">
        <div class="d-flex align-items-center gap-2 text-muted">
            @if(!empty($analyse->result_disponible['val_ref']))
               <i class="fas fa-info-circle"></i>
                <span class="me-2">Valeurs de référence: [{{ $analyse->result_disponible['val_ref'] }}]</span>
            @endif
            {{-- Ne pas afficher l'unité pour les leucocytes --}}
            @if(!empty($analyse->result_disponible['unite']) && $analyse->analyseType->name !== 'LEUCOCYTES')
                <span>{{ $analyse->result_disponible['unite'] }}</span>
            @endif
            @if(!empty($analyse->result_disponible['suffixe']))
                <span>{{ $analyse->result_disponible['suffixe'] }}</span>
            @endif
        </div>
    </div>
@endif

@if(!empty($analyse->description) && $analyse->analyseType->name !== 'LEUCOCYTES')
    <div class="alert alert-light mt-3 mb-0">
        <div class="d-flex align-items-start gap-2">
            <i class="fas fa-info-circle me-2 text-primary mt-1"></i>
            <div class="flex-grow-1">
                {!! nl2br(e($analyse->description)) !!}
            </div>
        </div>
    </div>
@endif


{{-- @if(is_array($analyse->result_disponible) &&
    (!empty($analyse->result_disponible['val_ref']) ||
    !empty($analyse->result_disponible['unite']) ||
    !empty($analyse->result_disponible['suffixe'])))
    <div class="mt-2 small">
        <div class="d-flex align-items-center gap-2 text-muted">
            <i class="fas fa-info-circle"></i>
            @if(!empty($analyse->result_disponible['val_ref']))
                <span class="me-2">Valeurs de référence: [{{ $analyse->result_disponible['val_ref'] }}]</span>
            @endif
            @if(!empty($analyse->result_disponible['unite']))
                <span>{{ $analyse->result_disponible['unite'] }}</span>
            @endif
            @if(!empty($analyse->result_disponible['suffixe']))
                <span>{{ $analyse->result_disponible['suffixe'] }}</span>
            @endif
        </div>
    </div>
@endif

@if(!empty($analyse->description))
    <div class="alert alert-light mt-3 mb-0">
        <div class="d-flex align-items-start gap-2">
            <i class="fas fa-info-circle me-2 text-primary mt-1"></i>
            <div class="flex-grow-1">
                {!! nl2br(e($analyse->description)) !!}
            </div>
        </div>
    </div>
@endif --}}
