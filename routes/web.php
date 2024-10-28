<?php

use App\Livewire\Dashboard;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Livewire\Admin\Users\EditUsers;
use App\Livewire\Secretaire\AddAnalyses;
use App\Livewire\Technicien\Traitements;
use App\Livewire\Admin\Analyses\Analyses;
use App\Livewire\Admin\Users\CreateUsers;
use App\Livewire\Admin\Examen\IndexExamen;
use App\Livewire\Admin\Types\AnalyseTypes;
use App\Livewire\Admin\Users\GestionUsers;
use App\Livewire\Admin\Users\ProfileCreation;
use App\Livewire\Secretaire\AddPrescriptions;
use App\Livewire\Secretaire\EditPrescription;
use App\Livewire\Admin\Analyses\AnalysesParent;
use App\Livewire\Admin\Analyses\AnalysesElement;
use App\Livewire\Secretaire\PatientPrescription;
use App\Livewire\Secretaire\ProfilePrescription;
use App\Livewire\Technicien\DetailsPrescription;
use App\Livewire\Admin\Analyses\AnalysesPrincipal;
use App\Livewire\Admin\Germes\BacteryFamilyManager;
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
            
            // Add specific biologiste routes here
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
        });

        // Prescripteur routes
        Route::middleware(['role:prescripteur'])->prefix('prescripteur')->name('prescripteur.')->group(function () {
            // Add specific prescripteur routes here
        });
    });
});
