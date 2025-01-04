@php
$count = \App\Models\Prescription::where('is_archive', true)
        ->where('status', \App\Models\Prescription::STATUS_ARCHIVE)
        ->count();
@endphp
<!-- Sidebar -->
<nav class="navbar-vertical navbar">
  <div class="vh-100" data-simplebar>
    <!-- Brand logo -->
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Orbitron:wght@500&display=swap');

        .la-reference-brand {
            font-family: 'Orbitron', sans-serif;
            color: #FF0000 !important; /* Rouge plus vif pour meilleur contraste */
            font-size: 18px; /* Taille réduite */
            letter-spacing: 0.5px;
            text-transform: uppercase;
            text-shadow: 1px 1px 1px rgba(0, 0, 0, 0.5); /* Ombre légère pour améliorer la lisibilité */
            font-weight: 800;
            padding: 8px 0;
        }

        /* Au survol */
        .la-reference-brand:hover {
            color: #FF3333 !important;
            text-decoration: none;
        }
        </style>

        <a class="navbar-brand d-flex align-items-center la-reference-brand" href="{{ route('dashboard') }}">
            La Reference
        </a>
    <!-- Navbar nav -->
    <ul class="navbar-nav flex-column" id="sideNavbar">
      <li class="nav-item">
        <a class="nav-link" href="{{ route('dashboard')}}">
          <i class="nav-icon fe fe-home me-2"></i>
          Tableau de bord
        </a>
      </li>

      @if(auth()->user()->can('biologiste'))
      <li class="nav-item">
        <div class="navbar-heading">Biologistes</div>
      </li>
      <li class="nav-item">
        <a class="nav-link"
           href="#"
           data-bs-toggle="collapse"
           data-bs-target="#navBiologiste"
           aria-expanded="true"
           aria-controls="navBiologiste">
          <i class="fas fa-user-md me-2"></i>
          Biologiste
        </a>
        <div id="navBiologiste" class="collapse show" data-bs-parent="#sideNavbar">
          <ul class="nav flex-column">
            <li class="nav-item">
              <a class="nav-link" href="{{ route('biologiste.analyse.index') }}">
                <i class="fas fa-check-circle me-2"></i> Analyses
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="{{ route('archives') }}">
                <i class="fas fa-archive me-2"></i>
                Archives
                <span class="badge rounded-pill bg-danger ms-2">
                  {{ $count }}
                </span>
              </a>
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
        <a class="nav-link"
           href="#"
           data-bs-toggle="collapse"
           data-bs-target="#navSec"
           aria-expanded="true"
           aria-controls="navSec">
          <i class="fas fa-laptop-medical me-2"></i>
          Secrétaire
        </a>
        <div id="navSec" class="collapse show" data-bs-parent="#sideNavbar">
          <ul class="nav flex-column">
            <li class="nav-item">
              <a class="nav-link" href="{{ route('secretaire.patients.index') }}">
                <i class="fas fa-user-plus me-2"></i>
                Prescriptions
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="{{ route('archives') }}">
                <i class="fas fa-archive me-2"></i>
                Archives
                <span class="badge rounded-pill bg-danger ms-2">{{ $count }}</span>
              </a>
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


      @if(auth()->user()->hasAnyRole(['superadmin', 'biologiste', 'technicien', 'secretaire']))
      <li class="nav-item">
          <div class="navbar-heading">Gestion des données</div>
      </li>
      <li class="nav-item">
          <a class="nav-link collapsed" href="#" data-bs-toggle="collapse"
              data-bs-target="#navGestionDonnees" aria-expanded="false" aria-controls="navGestionDonnees">
              <i class="nav-icon fe fe-database me-2"></i>
              Base de données
          </a>
          <div id="navGestionDonnees" class="collapse" data-bs-parent="#sideNavbar">
              <ul class="nav flex-column">
                  @if(auth()->user()->hasRole('superadmin'))
                  <li class="nav-item">
                      <a class="nav-link" href="{{route('admin.users.list')}}">
                          <i class="nav-icon fe fe-users me-2"></i> Utilisateurs
                      </a>
                  </li>
                  @endif

                  {{-- Accessible par superadmin, biologiste, technicien et secrétaire --}}
                  <li class="nav-item">
                      <a class="nav-link" href="{{ route('donnees.examen.list')}}">
                          <i class="nav-icon fe fe-folder me-2"></i> Examens
                      </a>
                  </li>
                  <li class="nav-item">
                      <a class="nav-link" href="{{ route('donnees.germes.list')}}">
                          <i class="nav-icon fe fe-folder me-2"></i> Germes
                      </a>
                  </li>
                  <li class="nav-item">
                      <a class="nav-link collapsed" href="#" data-bs-toggle="collapse"
                          data-bs-target="#navAnalyse" aria-expanded="false" aria-controls="navAnalyse">
                          <i class="nav-icon fe fe-folder me-2"></i> Analyses
                      </a>
                      <div id="navAnalyse" class="collapse" data-bs-parent="#navAnalyse">
                          <ul class="nav flex-column">
                              <li class="nav-item">
                                  <a class="nav-link" href="{{ route('donnees.types-analyse')}}">
                                      <i class="nav-icon fe fe-chevron-right me-2"></i> Types
                                  </a>
                              </li>
                              <li class="nav-item">
                                  <a class="nav-link" href="{{route('donnees.analyse.list')}}">
                                      <i class="nav-icon fe fe-chevron-right me-2"></i> Principales
                                  </a>
                              </li>
                          </ul>
                      </div>
                  </li>
              </ul>
          </div>
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
                <p class="text-white-50">La reference - version 1.5.0 - Bêta</p>
                <a href="https://github.com/GasyCoder" target="_blank" class="mt-0 badge bg-secondary-soft">Developed by GasyCoder</a>
            </div>
        </div>
    </div>
  </div>
</nav>
