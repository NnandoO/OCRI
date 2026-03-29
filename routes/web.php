<?php

use App\Http\Controllers\AgreementController;
use App\Http\Controllers\InstitutionController;
use Illuminate\Support\Facades\Route;

// Página de inicio (Login)
Route::view('/', 'pages.auth.login')->name('home');

// Rutas Protegidas (Solo para usuarios logueados)
Route::middleware(['auth', 'verified'])->group(function () {
    
    // Dashboard
    Route::view('dashboard', 'dashboard')->name('dashboard');

    // Módulo de Convenios
    Route::controller(AgreementController::class)->group(function () {
        Route::get('/agreements', 'index')->name('agreements.index');           // Directorio
        Route::get('/agreements/create', 'create')->name('agreements.create');   // Formulario
        Route::post('/agreements', 'store')->name('agreements.store');          // Guardar
        Route::get('/agreements/process', 'process')->name('agreements.process'); // Mapa de Procesos
    });

    // Módulo de Instituciones
    Route::get('/institutions', [InstitutionController::class, 'index'])->name('institutions.index');

    // Reportes e Indicadores
    Route::get('/reports', function () { 
        return view('reports.index'); 
    })->name('reports.index');

});

require __DIR__.'/settings.php';
