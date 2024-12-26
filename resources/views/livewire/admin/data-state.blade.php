<div>
    <div class="row gy-4 mb-4">
        <!-- Revenus totaux -->
        <div class="col-xl-8 col-lg-12 col-md-12 col-12">
            <div class="card">
                <div class="card-header align-items-center card-header-height d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-0">Revenus</h4>
                    </div>
                    <div>
                        <div class="dropdown dropstart">
                            <a class="btn-icon btn btn-ghost btn-sm rounded-circle" href="#" role="button" id="revenueDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fe fe-more-vertical"></i>
                            </a>
                            <div class="dropdown-menu" aria-labelledby="revenueDropdown">
                                <span class="dropdown-header">Paramètres</span>
                                <a class="dropdown-item" href="#">
                                    <i class="fe fe-external-link dropdown-item-icon"></i>
                                    Exporter
                                </a>
                                <a class="dropdown-item" href="#">
                                    <i class="fe fe-mail dropdown-item-icon"></i>
                                    Envoyer par Email
                                </a>
                                <a class="dropdown-item" href="#">
                                    <i class="fe fe-download dropdown-item-icon"></i>
                                    Télécharger
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div id="revenueChart" class="apex-charts"></div>
                </div>
            </div>
        </div>
        <!-- Activité récente -->
        <div class="col-xl-4 col-lg-12 col-md-12 col-12">
            <div class="card">
                <div class="card-header align-items-center card-header-height d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-0">Activité récente</h4>
                    </div>
                    <div>
                        <div class="dropdown dropstart">
                            <a class="btn-icon btn btn-ghost btn-sm rounded-circle" href="#" role="button" id="activityDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fe fe-more-vertical"></i>
                            </a>
                            <div class="dropdown-menu" aria-labelledby="activityDropdown">
                                <span class="dropdown-header">Paramètres</span>
                                <a class="dropdown-item" href="#">
                                    <i class="fe fe-external-link dropdown-item-icon"></i>
                                    Exporter
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div id="activityChart" class="apex-charts d-flex justify-content-center"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row gy-4">
        <!-- Meilleures analyses -->
        <div class="col-xl-4 col-lg-12 col-md-12 col-12">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between card-header-height">
                    <h4 class="mb-0">Analyses les plus demandées</h4>
                    <a href="#" class="btn btn-outline-secondary btn-sm">Voir tout</a>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item px-0 pt-0">
                            <div class="row">
                                <div class="col ms-3">
                                    <h4 class="mb-0 h5">Hématologie</h4>
                                    <span class="fs-6">Demandé 1,204 fois</span>
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item px-0">
                            <div class="row">
                                <div class="col ms-3">
                                    <h4 class="mb-0 h5">Biochimie</h4>
                                    <span class="fs-6">Demandé 980 fois</span>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- Patients récents -->
        <div class="col-xl-4 col-lg-12 col-md-12 col-12">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between card-header-height">
                    <h4 class="mb-0">Patients récents</h4>
                    <a href="#" class="btn btn-outline-secondary btn-sm">Voir tout</a>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item px-0 pt-0">
                            <div class="row">
                                <div class="col ms-3">
                                    <h4 class="mb-0 h5">John Doe</h4>
                                    <span class="fs-6">Analyse : Hémoglobine</span>
                                    <span class="fs-6">Résultat prêt</span>
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item px-0">
                            <div class="row">
                                <div class="col ms-3">
                                    <h4 class="mb-0 h5">Jane Smith</h4>
                                    <span class="fs-6">Analyse : Glucose</span>
                                    <span class="fs-6">En cours</span>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- Activités récentes -->
        <div class="col-xl-4 col-lg-12 col-md-12 col-12">
            <div class="card h-100">
                <div class="card-header card-header-height d-flex align-items-center">
                    <h4 class="mb-0">Activités</h4>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item px-0 pt-0">
                            <div class="row">
                                <div class="col ms-3">
                                    <h4 class="mb-0 h5">Prélèvement effectué</h4>
                                    <span class="fs-6">Patient : Marie Curie</span>
                                    <span class="fs-6">Type : Biochimie</span>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
