{{-- views/livewire/secretaire/steps/progress-bar.blade.php --}}
<div class="progress-wrapper mb-4">
    <div class="progress mb-3" style="height: 5px;">
        <div class="progress-bar bg-primary" role="progressbar"
                style="width: {{ $progressPercentage }}%; transition: width 0.3s ease;"
                aria-valuenow="{{ $progressPercentage }}"
                aria-valuemin="0"
                aria-valuemax="100">
        </div>
    </div>
    <div class="row text-center g-0">
        @foreach(['Information Patient', 'Information Médicale', 'Analyses & Prélèvements'] as $index => $stepName)
            <div class="col {{ $index === 1 ? 'border-start border-end' : '' }}">
                <div class="position-relative">
                    <div class="rounded-circle bg-{{ $step > ($index + 1) ? 'success' : ($step === ($index + 1) ? 'primary' : 'secondary') }} d-flex align-items-center justify-content-center mx-auto mb-2"
                            style="width: 32px; height: 32px;">
                        @if($step > ($index + 1))
                            <i class="fas fa-check text-white small"></i>
                        @else
                            <span class="text-white">{{ $index + 1 }}</span>
                        @endif
                    </div>
                    <span class="badge {{ $step >= ($index + 1) ? 'bg-primary' : 'bg-secondary' }} rounded-pill">
                        {{ $stepName }}
                    </span>
                </div>
            </div>
        @endforeach
    </div>
</div>

@push('styles')
<style>
    .progress {
        border-radius: 10px;
        overflow: hidden;
        background-color: #e9ecef;
        box-shadow: inset 0 1px 2px rgba(0,0,0,.1);
    }

    .progress-bar {
        transition: width 0.6s ease;
    }

    .progress-step .badge {
        width: 25px;
        height: 25px;
        line-height: 25px;
        padding: 0;
        font-size: 0.875rem;
    }

    .bg-success { background-color: #28a745 !important; }
    .bg-primary { background-color: #0d6efd !important; }
    .bg-secondary { background-color: #6c757d !important; }
</style>
@endpush
