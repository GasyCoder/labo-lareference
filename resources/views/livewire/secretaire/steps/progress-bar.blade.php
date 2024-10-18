<div class="progress-wrapper mb-4">
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
    <div class="progress-steps d-flex justify-content-between position-relative" style="margin-top: -14px;">
        @for ($i = 1; $i <= $totalSteps; $i++)
            <div class="progress-step" data-bs-toggle="tooltip" title="Étape {{ $i }}">
                <span class="badge rounded-pill {{ $i <= $step ? 'bg-primary fw-bold' : 'bg-secondary' }}">
                    {{ $i }}
                </span>
            </div>
        @endfor
    </div>
</div>
