<?php

use App\Http\Controllers\AgreementController;
use App\Http\Controllers\InstitutionController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReportController;

// Página de inicio (Login)
Route::view('/', 'pages.auth.login')->name('home');

// Rutas Protegidas (Solo para usuarios logueados)
Route::middleware(['auth', 'verified'])->group(function () {
    
    // Dashboard
    Route::get('dashboard', [AgreementController::class, 'dashboard'])->name('dashboard');

    Route::controller(AgreementController::class)->group(function () {
        Route::get('/agreements', 'index')->name('agreements.index');
        Route::get('/agreements/create', 'create')->name('agreements.create');
        Route::post('/agreements', 'store')->name('agreements.store');
        Route::get('/agreements/{agreement}', 'show')->name('agreements.show');
        
        // --- RUTAS DE EDICIÓN AÑADIDAS AQUÍ ---
        Route::get('/agreements/{agreement}/edit', 'edit')->name('agreements.edit');
        Route::put('/agreements/{agreement}', 'update')->name('agreements.update');
        // --------------------------------------

        Route::patch('/agreements/{agreement}/status', 'updateStatus')->name('agreements.updateStatus');
        Route::patch('/agreements/{agreement}/status', 'updateStatus')->name('agreements.update-status');
        
        Route::post('/agreements/{agreement}/roadmap', 'storeRoadmap')->name('agreements.roadmap.store');
        Route::patch('/agreements/roadmap/{itemId}/check', 'checkRoadmapItem')->name('agreements.roadmap.check');
        
        Route::delete('/agreements/{agreement}', 'destroy')->name('agreements.destroy');
    });

    Route::patch('/agreements/{agreement}/activate', [AgreementController::class, 'activate'])->name('agreements.activate');

    // Módulo de Instituciones
    Route::resource('institutions', InstitutionController::class);

    // Reportes e Indicadores (Corregido: Usando solo el controlador para que funcione la búsqueda)
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
});

require __DIR__.'/settings.php';