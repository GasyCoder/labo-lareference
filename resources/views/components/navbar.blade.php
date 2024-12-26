    <nav class="navbar-default navbar navbar-expand-lg">
        <a id="nav-toggle" href="#">
            <i class="fe fe-menu"></i>
        </a>
        <!--Navbar nav -->
        <div class="ms-auto d-flex">
            <div class="dropdown">
                <button class="btn btn-light btn-icon rounded-circle d-flex align-items-center" type="button" aria-expanded="false" data-bs-toggle="dropdown" aria-label="Toggle theme (auto)">
                    <i class="fas fa-adjust"></i>
                    <span class="visually-hidden bs-theme-text">Toggle theme</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="bs-theme-text">
                    <li>
                        <button type="button" class="dropdown-item d-flex align-items-center" data-bs-theme-value="light" aria-pressed="false">
                            <i class="far fa-lightbulb"></i>
                            <span class="ms-2">Claire</span>
                        </button>
                    </li>
                    <li>
                        <button type="button" class="dropdown-item d-flex align-items-center" data-bs-theme-value="dark" aria-pressed="false">
                            <i class="fas fa-lightbulb"></i>
                            <span class="ms-2">Sombre</span>
                        </button>
                    </li>
                    <li>
                        <button type="button" class="dropdown-item d-flex align-items-center active" data-bs-theme-value="auto" aria-pressed="true">
                            <i class="fas fa-adjust"></i>
                            <span class="ms-2">Auto</span>
                        </button>
                    </li>
                </ul>
            </div>
            <ul class="navbar-nav navbar-right-wrap ms-2 d-flex nav-top-wrap">
                <!-- List -->
                <li class="dropdown ms-2">
                    <a class="rounded-circle" href="#" role="button" id="dropdownUser" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="avatar avatar-md avatar-indicators avatar-online">
                            <div class="rounded-circle d-flex align-items-center justify-content-center bg-primary text-white" style="width: 40px; height: 40px;">
                                {{ strtoupper(substr(Auth()->user()->name, 0, 2)) }}
                            </div>
                        </div>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownUser">
                        <div class="dropdown-item">
                            <div class="d-flex">
                                <div class="avatar avatar-md avatar-indicators avatar-online">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center bg-primary text-white" style="width: 40px; height: 40px;">
                                        {{ strtoupper(substr(Auth()->user()->name, 0, 2)) }}
                                    </div>
                                </div>
                                <div class="ms-3 lh-1">
                                    <h5 class="mb-1">{{Auth()->user()->name}}</h5>
                                    <p class="mb-0">{{Auth()->user()->email}}</p>
                                </div>
                            </div>
                        </div>
                        <div class="dropdown-divider"></div>
                        <ul class="list-unstyled">
                            <li>
                                <a class="dropdown-item" href="#">
                                    <i class="fe fe-user me-2"></i>
                                    Profile
                                </a>
                            </li>
                        </ul>
                        <div class="dropdown-divider"></div>
                        <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <ul class="list-unstyled">
                            <li>
                                <a class="dropdown-item" href="route('logout')"
                                        onclick="event.preventDefault();
                                        this.closest('form').submit();">
                                    <i class="fe fe-power me-2"></i>
                                    Se deconnecter
                                </a>
                            </li>
                        </ul>
                        </form>
                    </div>
                </li>
            </ul>
        </div>
    </nav>
