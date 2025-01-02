<div class="mt-2 small">
    @if(is_array($analyse->result_disponible))
        <div class="d-flex align-items-center gap-2 text-muted">
            @if(!empty($analyse->result_disponible['val_ref']))
                <i class="fas fa-info-circle"></i>
                <span class="me-2">Valeurs de référence: {{ $analyse->result_disponible['val_ref'] }}</span>
            @endif

            @if(!empty($analyse->result_disponible['unite']) && $analyse->analyseType->name !== 'LEUCOCYTES')
                <span class="badge bg-light text-dark">{{ $analyse->result_disponible['unite'] }}</span>
            @endif

            @if(!empty($analyse->result_disponible['suffixe']))
                <span class="badge bg-light text-dark">{{ $analyse->result_disponible['suffixe'] }}</span>
            @endif
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
</div>
