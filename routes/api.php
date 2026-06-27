<?php

use App\Http\Controllers\Api\EstablecimientoApiController;
use App\Http\Controllers\Api\HistoriaClinicaApiController;
use App\Http\Controllers\Api\MedicamentoApiController;
use App\Http\Controllers\Api\NotaMedicaApiController;
use App\Http\Controllers\Api\PacienteApiController;
use Illuminate\Support\Facades\Route;

/*
|------------------------------------------------------------------------------
| API Routes — v1
|------------------------------------------------------------------------------
|
| Prefijo /api/v1 (definido en bootstrap/app.php → apiPrefix o en este archivo).
| Protegidas por el mismo middleware 'firebase', que acepta Bearer token o
| sesión. window.App.post('pacientes', ...) pega aquí.
|
| Registro en bootstrap/app.php (Laravel 13):
|   ->withRouting(
|       web: __DIR__.'/../routes/web.php',
|       api: __DIR__.'/../routes/api.php',
|       apiPrefix: 'api/v1',
|       commands: __DIR__.'/../routes/console.php',
|   )
|
*/

Route::middleware('firebase')->group(function () {
    Route::post('/pacientes',                      [PacienteApiController::class, 'store'])->name('api.pacientes.store');
    Route::put('/pacientes/{paciente}',            [PacienteApiController::class, 'update'])->name('api.pacientes.update');
    Route::delete('/pacientes/{paciente}',         [PacienteApiController::class, 'destroy'])->name('api.pacientes.destroy');
    Route::post('/pacientes/{paciente}/reactivar', [PacienteApiController::class, 'reactivar'])->name('api.pacientes.reactivar');

    // Historia clínica (1:1 con el paciente)
    Route::put('/pacientes/{paciente}/historia', [HistoriaClinicaApiController::class, 'save'])->name('api.historias.save');

    // Notas médicas
    Route::post('/pacientes/{paciente}/notas',                [NotaMedicaApiController::class, 'store'])->name('api.notas.store');
    Route::put('/pacientes/{paciente}/notas/{nota}',          [NotaMedicaApiController::class, 'update'])->name('api.notas.update');
    Route::post('/pacientes/{paciente}/notas/{nota}/firmar',  [NotaMedicaApiController::class, 'firmar'])->name('api.notas.firmar');
});

/* ── Gestión de catálogos (solo admin) ──────────────────────────────────── */
Route::middleware('firebase:admin')->group(function () {
    Route::post('/medicamentos',                       [MedicamentoApiController::class, 'store'])->name('api.medicamentos.store');
    Route::put('/medicamentos/{medicamento}',          [MedicamentoApiController::class, 'update'])->name('api.medicamentos.update');
    Route::delete('/medicamentos/{medicamento}',       [MedicamentoApiController::class, 'destroy'])->name('api.medicamentos.destroy');
    Route::post('/medicamentos/{medicamento}/reactivar', [MedicamentoApiController::class, 'reactivar'])->name('api.medicamentos.reactivar');

    Route::post('/establecimientos',                      [EstablecimientoApiController::class, 'store'])->name('api.establecimientos.store');
    Route::put('/establecimientos/{establecimiento}',     [EstablecimientoApiController::class, 'update'])->name('api.establecimientos.update');
    Route::delete('/establecimientos/{establecimiento}',  [EstablecimientoApiController::class, 'destroy'])->name('api.establecimientos.destroy');
});
