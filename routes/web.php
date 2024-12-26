<?php

use App\Livewire\Dashboard;
use App\Livewire\ExamenTest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PDFController;
use App\Livewire\Admin\Users\EditUsers;
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
use App\Livewire\ArchivedPrescriptions;
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

// Protected routes
Route::middleware(['auth', 'verified'])->group(function () {

    // Role-specific routes
    Route::group(['middleware' => ['role:superadmin|biologiste|secretaire|technicien|prescripteur']], function () {
        Route::get('/dashboard', Dashboard::class)->name('dashboard');

        Route::get('/preview-pdf/{filename}', function ($filename) {
            $path = storage_path('app/public/temp/' . $filename);

            if (!file_exists($path)) {
                abort(404, 'PDF non trouvé');
            }

            return response()->file($path, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $filename . '"',
            ]);
        })->name('preview.pdf');

        Route::get('/examens', ExamenTest::class)->name('showExamens');
        Route::get('/archives', ArchivedPrescriptions::class)->name('archives');
        // Super Admin routes
        Route::middleware(['role:superadmin'])->prefix('admin')->name('admin.')->group(function () {
            // Add specific admin routes here
            Route::get('/utilisateurs', GestionUsers::class)->name('users.list');
            Route::get('/create', CreateUsers::class)->name('users.create');
            Route::get('/edit/utilisateur/{user}', EditUsers::class)->name('users.edit');

            Route::get('/examen', IndexExamen::class)->name('examen.list');
            Route::get('/germes', BacteryFamilyManager::class)->name('germes.list');



            Route::get('/types-analyse', AnalyseTypes::class)->name('types-analyse');
            Route::get('/analyse-principal', Analyses::class)->name('analyse.list');
            Route::get('/analyse-principal/view/{id}', Analyses::class)->name('analyse.view');
            Route::get('/analyse-element/create', AnalysesElement::class)->name('analyse-element.create');
        });

        // Biologiste routes
        Route::middleware(['role:biologiste'])->prefix('biologiste')->name('biologiste.')->group(function () {
            Route::get('/analyse-valide', AnalyseValide::class)->name('analyse.index');
            Route::get('/valide/{prescription}/analyse', BiologisteAnalysisForm::class)->name('valide.show');
        });

        // Secrétaire routes
        Route::middleware(['role:secretaire'])->prefix('secretaire')->name('secretaire.')->group(function () {

            Route::get('/prescriptions', PatientPrescription::class)->name('patients.index');
            Route::get('/prescriptions/ajouter', AddPrescriptions::class)->name('prescriptions.add');
            Route::get('/prescriptions/{id}/edit', EditPrescription::class)->name('prescriptions.edit');
            Route::get('/prescriptions/{id}/profil', ProfilePrescription::class)->name('prescriptions.profil');

        });

        // Technicien routes
        Route::middleware(['auth', 'role:technicien'])->prefix('technicien')->name('technicien.')->group(function () {
            Route::get('/traitement', Traitements::class)->name('traitement.index');
            Route::get('/traitement/{prescription}/analyse', TechnicianAnalysisForm::class)->name('traitement.show');


            Route::get('/analyse-pdf/{prescription}/{analyse}', [PDFController::class, 'generateAnalysePDF'])
            ->name('analyse.pdf');
        });

        // Prescripteur routes
        Route::middleware(['role:prescripteur'])->prefix('prescripteur')->name('prescripteur.')->group(function () {
            // Add specific prescripteur routes here
        });
    });
});
