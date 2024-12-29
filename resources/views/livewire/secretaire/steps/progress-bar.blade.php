{{-- views/livewire/secretaire/steps/progress-bar.blade.php --}}
<div class="progress-wrapper mb-4">
    {{-- Progress Bar --}}
    <div class="progress" style="height: 25px;">
        @for ($i = 1; $i <= $totalSteps; $i++)
            <div class="progress-bar {{ $i < $step ? 'bg-success' : ($i == $step ? 'bg-primary' : 'bg-secondary') }}"
                role="progressbar"
                style="width: {{ 100 / $totalSteps }}%"
                aria-valuenow="{{ 100 / $totalSteps }}"
                aria-valuemin="0"
                aria-valuemax="100">
            </div>
        @endfor
    </div>

    {{-- Step Indicators --}}
    <div class="progress-steps d-flex justify-content-between position-relative mt-n2">
        @for ($i = 1; $i <= $totalSteps; $i++)
            <div class="progress-step">
                <span class="badge rounded-pill {{ $i <= $step ? 'bg-primary' : 'bg-secondary' }} shadow-sm">
                    {{ $i }}
                </span>
            </div>
        @endfor
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
