<div>
    <!-- Container fluid -->
    <section class="container-fluid p-4">
        <div class="row">
        <div class="col-lg-12 col-md-12 col-12">
            <div class="border-bottom pb-3 mb-3 d-flex flex-column flex-lg-row gap-3 justify-content-between align-items-lg-center">
            <div>
                <h1 class="mb-0 h2 fw-bold">Dashboard</h1>
            </div>
            <div class="d-flex gap-3">
                <div class="input-group">
                <input class="form-control flatpickr" type="text" placeholder="Select Date" aria-describedby="basic-addon2" />

                <span class="input-group-text" id="basic-addon2"><i class="fe fe-calendar"></i></span>
                </div>
                <a href="#" class="btn btn-primary">Setting</a>
            </div>
            </div>
        </div>
        </div>

        <livewire:admin.data-counter />

        @if(auth()->user()->hasRole('superadmin') || auth()->user()->can('superadmin'))
            <livewire:admin.data-state />
        @endif

    </section>
    </div>
