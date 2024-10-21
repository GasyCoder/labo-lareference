<!-- Sidebar -->
<nav class="navbar-vertical navbar">
  <div class="vh-100" data-simplebar>
    <!-- Brand logo -->
    <a class="navbar-brand d-flex align-items-center" href="{{ route('dashboard') }}">
        <img src="{{ asset('assets/images/brand/logo/logo.png') }}" alt="Logo" height="40" class="me-2" />
        La reference
      </a>
    <!-- Navbar nav -->
    <ul class="navbar-nav flex-column" id="sideNavbar">
      <li class="nav-item">
        <a class="nav-link" href="{{ route('dashboard')}}">
          <i class="nav-icon fe fe-home me-2"></i>
          Tableau de bord
        </a>
      </li>

      @if(auth()->user()->hasRole('superadmin') || auth()->user()->can('superadmin'))
      <li class="nav-item">
        <div class="navbar-heading">Super Admin</div>
      </li>
      <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-bs-toggle="collapse"
          data-bs-target="#navAdmin" aria-expanded="false" aria-controls="navAdmin">
          <i class="nav-icon fe fe-settings me-2"></i>
          Administration
        </a>
        <div id="navAdmin" class="collapse" data-bs-parent="#sideNavbar">
          <ul class="nav flex-column">
            <li class="nav-item">
              <a class="nav-link" href="{{route('admin.users.list')}}">
                  <i class="nav-icon fe fe-users me-2"></i> Utilisateurs
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="{{ route('admin.examen.list')}}">
                <i class="nav-icon fe fe-folder me-2"></i> Gestion Examens
              </a>
            </li>
             <li class="nav-item">
              <a class="nav-link" href="{{ route('admin.germes.list')}}">
                <i class="nav-icon fe fe-folder me-2"></i> Germes
              </a>
            </li>
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#navAnalyse" aria-expanded="false" aria-controls="navAnalyse">
                    <i class="nav-icon fe fe-folder me-2"></i> Analyses
                </a>
                <div id="navAnalyse" class="collapse" data-bs-parent="#navAnalyse" style="">
                  <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.types-analyse')}}"><i class="nav-icon fe fe-chevron-right me-2"></i>
                            Types</a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link" href="{{route('admin.analyse.list')}}"><i class="nav-icon fe fe-chevron-right me-2"></i>
                        Principales </a>
                    </li>
                  </ul>
                </div>
            </li>
          </ul>
        </div>
      </li>
      @endif

      @if(auth()->user()->can('biologiste'))
      <li class="nav-item">
        <div class="navbar-heading">Biologistes</div>
      </li>
      <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-bs-toggle="collapse"
          data-bs-target="#navBiologiste" aria-expanded="false" aria-controls="navBiologiste">
          <i class="fas fa-user-md me-2"></i>
          Biologiste
        </a>
        <div id="navBiologiste" class="collapse" data-bs-parent="#sideNavbar">
          <ul class="nav flex-column">
             <li class="nav-item">
              <a class="nav-link" href="#">
                <i class="fas fa-check-circle me-2"></i> Analyses valides</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="#"><i class="fas fa-archive me-2"></i> Archives</a>
            </li>
          </ul>
        </div>
      </li>
      @endif

      @if(auth()->user()->can('secretaire'))
      <li class="nav-item">
        <div class="navbar-heading">Secrétaires</div>
      </li>
      <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-bs-toggle="collapse"
          data-bs-target="#navSec" aria-expanded="false" aria-controls="navSec">
          <i class="fas fa-laptop-medical me-2"></i>
          Secrétaire
        </a>
        <div id="navSec" class="collapse" data-bs-parent="#sideNavbar">
          <ul class="nav flex-column">
            <li class="nav-item">
              <a class="nav-link" href="{{route('secretaire.patients.index')}}"><i class="fas fa-user-plus me-2"></i> Prescriptions</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="#"><i class="fas fa-archive me-2"></i> Archives</a>
            </li>
          </ul>
        </div>
      </li>
      @endif

      @if(auth()->user()->can('technicien'))
      <li class="nav-item">
        <div class="navbar-heading">Techniciens</div>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="{{route('technicien.traitement.index')}}">
         <i class="fas fa-microscope me-2"></i>
          Techniciens
        </a>
      </li>
      @endif

      @if(auth()->user()->hasRole('superadmin'))
      <li class="nav-item">
        <a class="nav-link" href="#">
          <i class="nav-icon fe fe-settings me-2"></i>
          Paramètres
        </a>
      </li>
      @endif
    </ul>

    <hr>
    <!-- Card -->
    <div class="mx-4 my-8 shadow-none text-start">
        <div class="py-6">
            <div class="mt-8">
                <p class="text-white-50">version 1.3.0</p>
                <a href="#" class="mt-0 badge bg-secondary-soft">Developed by GasyCoder</a>
            </div>
        </div>
    </div>
  </div>
</nav>
