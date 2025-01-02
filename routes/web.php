<?php

use App\Livewire\Dashboard;
use App\Models\Prescription;
use App\Services\ResultatPdfService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PDFController;
use App\Livewire\Admin\Users\EditUsers;
use App\Livewire\ArchivedPrescriptions;
use App\Livewire\Technicien\Traitements;
use App\Livewire\Admin\Analyses\Analyses;
use App\Livewire\Admin\Users\CreateUsers;
use App\Livewire\Admin\Examen\IndexExamen;
use App\Livewire\Admin\Types\AnalyseTypes;
use App\Livewire\Admin\Users\GestionUsers;
use App\Livewire\Biologiste\AnalyseValide;
use App\Livewire\Secretaire\AddPrescriptions;
use App\Livewire\Secretaire\EditPrescription;
use App\Livewire\Admin\Analyses\AnalysesElement;
use App\Livewire\Secretaire\PatientPrescription;
use App\Livewire\Secretaire\ProfilePrescription;
use App\Livewire\Admin\Germes\BacteryFamilyManager;
use App\Livewire\Biologiste\BiologisteAnalysisForm;
use App\Livewire\Technicien\TechnicianAnalysisForm;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Authentication routes
require __DIR__.'/auth.php';
Route::redirect('/', '/login');
Route::redirect('/register', '/login');

Route::get('/', function () {
    return Auth::check() ? redirect('/dashboard') : redirect('/login');
});

// Routes publiques avant les routes protégées
Route::get('/resultats/public/{prescription}/{hash}', function (Prescription $prescription, $hash) {
    if ($hash !== md5($prescription->created_at->timestamp)) {
        abort(404, 'Lien invalide');
    }

    $pdfService = app(ResultatPdfService::class);
    $pdf = $pdfService->generatePDF($prescription);

    return $pdf->stream('resultats.pdf');
})->name('resultats.public-view');


// Protected routes
Route::middleware(['auth', 'verified'])->group(function () {

    // Role-specific routes
    Route::group(['middleware' => ['role:superadmin|biologiste|secretaire|technicien|prescripteur']], function () {
        Route::get('/dashboard', Dashboard::class)->name('dashboard');

        Route::get('/archives', ArchivedPrescriptions::class)->name('archives');

        // Routes partagées avec préfixe selon le rôle de l'utilisateur
        Route::middleware(['auth', 'role:superadmin|biologiste|technicien|secretaire'])->group(function () {
            Route::group([], function () {
                Route::get('/donnees/examens', IndexExamen::class)->name('donnees.examen.list');
                Route::get('/donnees/germes', BacteryFamilyManager::class)->name('donnees.germes.list');

                // Routes Analyses
                Route::get('/donnees/types-analyse', AnalyseTypes::class)->name('donnees.types-analyse');
                Route::get('/donnees/analyses', Analyses::class)->name('donnees.analyse.list');
                Route::get('/donnees/analyses/{id}/view', Analyses::class)->name('donnees.analyse.view');
                Route::get('/donnees/analyses/element/create', AnalysesElement::class)->name('donnees.analyse-element.create');
            });
        });

        // Routes spécifiques au superadmin uniquement
        Route::middleware(['auth', 'role:superadmin'])->prefix('admin')->name('admin.')->group(function () {
            Route::get('/utilisateurs', GestionUsers::class)->name('users.list');
            Route::get('/create', CreateUsers::class)->name('users.create');
            Route::get('/edit/utilisateur/{user}', EditUsers::class)->name('users.edit');
        });

        // Routes spécifiques au Biologiste uniquement
        Route::middleware(['role:biologiste'])->prefix('biologiste')->name('biologiste.')->group(function () {
            Route::get('/analyse-valide', AnalyseValide::class)->name('analyse.index');
            Route::get('/valide/{prescription}/analyse', BiologisteAnalysisForm::class)->name('valide.show');
        });

        // Routes spécifiques au Secrétaire uniquement
        Route::middleware(['role:secretaire'])->prefix('secretaire')->name('secretaire.')->group(function () {

            Route::get('/prescriptions', PatientPrescription::class)->name('patients.index');
            Route::get('/prescriptions/ajouter', AddPrescriptions::class)->name('prescriptions.add');
            Route::get('/prescriptions/{id}/edit', EditPrescription::class)->name('prescriptions.edit');
            Route::get('/prescriptions/{id}/profil', ProfilePrescription::class)->name('prescriptions.profil');

        });

        // Routes spécifiques au Technicien uniquement
        Route::middleware(['auth', 'role:technicien'])->prefix('technicien')->name('technicien.')->group(function () {
            Route::get('/traitement', Traitements::class)->name('traitement.index');
            Route::get('/traitement/{prescription}/analyse', TechnicianAnalysisForm::class)->name('traitement.show');


            Route::get('/analyse-pdf/{prescription}/{analyse}', [PDFController::class, 'generateAnalysePDF'])
            ->name('analyse.pdf');
        });

        // Routes spécifiques au Prescripteur routes
        Route::middleware(['role:prescripteur'])->prefix('prescripteur')->name('prescripteur.')->group(function () {
            // Add specific prescripteur routes here
        });
    });
});
