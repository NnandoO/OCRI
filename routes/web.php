<?php

use App\Http\Controllers\AgreementController;
use App\Http\Controllers\InstitutionController;
use App\Http\Controllers\OficioController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReportController;

// Página de inicio (Login)
Route::view('/', 'pages.auth.login')->name('home');

// Rutas Protegidas (Solo para usuarios logueados)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::post('/agreements/roadmap/{item}/upload', [AgreementController::class, 'uploadDocument'])->name('agreements.roadmap.upload');
    Route::delete('/agreements/roadmap/document/{document}', [AgreementController::class, 'deleteDocument'])->name('agreements.roadmap.delete-doc');
    Route::post('/api/institutions', [InstitutionController::class, 'store']);
    Route::post('/agreements/{agreement}/upload-main', [AgreementController::class, 'uploadMainDocument'])->name('agreements.upload-main');
    Route::delete('/documents/{id}', [AgreementController::class, 'destroyMainDocument'])->name('documents.destroy');
    
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
        
        Route::post('/agreements/{agreement}/roadmap', 'storeRoadmap')->name('agreements.roadmap.store');
        Route::patch('/agreements/roadmap/{item}/envio', 'updateEnvio')->name('agreements.roadmap.envio');
        Route::patch('/agreements/roadmap/{itemId}/check', 'checkRoadmapItem')->name('agreements.roadmap.check');
        
        Route::delete('/agreements/{agreement}', 'destroy')->name('agreements.destroy');
    });

    Route::patch('/agreements/{agreement}/activate', [AgreementController::class, 'activate'])->name('agreements.activate');

    // Módulo de Oficios
    Route::get('/agreements/{agreement}/oficios', [OficioController::class, 'create'])->name('agreements.oficios.create');
    Route::post('/agreements/{agreement}/oficios', [OficioController::class, 'store'])->name('agreements.oficios.store');
    Route::post('/agreements/{agreement}/expediente-final', [OficioController::class, 'generateExpedienteFinal'])->name('agreements.expediente-final');
    Route::get('/oficios/{oficio}/download', [OficioController::class, 'download'])->name('oficios.download');
    Route::get('/oficios/{oficio}/edit', [OficioController::class, 'edit'])->name('oficios.edit');
    Route::put('/oficios/{oficio}', [OficioController::class, 'update'])->name('oficios.update');

    // Módulo de Instituciones
    Route::resource('institutions', InstitutionController::class);

    // Reportes e Indicadores
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

    // Módulo de Seguimiento
    Route::controller(\App\Http\Controllers\SeguimientoController::class)->group(function () {
        Route::get('/seguimiento', 'index')->name('seguimiento.index');
        Route::get('/seguimiento/{agreement}', 'show')->name('seguimiento.show');
        Route::post('/seguimiento/{agreement}/plan', 'storePlan')->name('seguimiento.storePlan');
        Route::post('/seguimiento/{agreement}/report', 'storeReport')->name('seguimiento.storeReport');
    });

    Route::middleware([\App\Http\Middleware\CheckAdminRole::class])->group(function () {
        // Módulo de Asistencia
        Route::controller(\App\Http\Controllers\AsistenciaController::class)->group(function () {
            Route::get('/asistencia', 'index')->name('asistencia.index');
            Route::post('/asistencia', 'store')->name('asistencia.store');
            Route::post('/asistencia/regularizar', 'regularizar')->name('asistencia.regularizar');
            Route::patch('/asistencia/{asistencia}/salida', 'marcarSalida')->name('asistencia.salida');
            Route::delete('/asistencia/{asistencia}', 'destroy')->name('asistencia.destroy');
        });

        // Módulo de Practicantes
        Route::post('/practicantes', [\App\Http\Controllers\PracticanteController::class, 'store'])->name('practicantes.store');
        Route::get('/practicantes/{practicante}', [\App\Http\Controllers\PracticanteController::class, 'show'])->name('practicantes.show');
        Route::get('/practicantes/{practicante}/export', [\App\Http\Controllers\PracticanteController::class, 'export'])->name('practicantes.export');
    });
});

require __DIR__.'/settings.php';