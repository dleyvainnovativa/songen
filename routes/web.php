<?php

use App\Http\Controllers\AuditoriaController;
use App\Http\Controllers\Auth\FirebaseAuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentoController;
use App\Http\Controllers\EstablecimientoPageController;
use App\Http\Controllers\HistoriaClinicaPageController;
use App\Http\Controllers\ImpresionController;
use App\Http\Controllers\MedicamentoPageController;
use App\Http\Controllers\NotaMedicaPageController;
use App\Http\Controllers\PacientePageController;
use Illuminate\Support\Facades\Route;

/*
|------------------------------------------------------------------------------
| Web Routes
|------------------------------------------------------------------------------
|
| El alias 'firebase' se registra en bootstrap/app.php (ver README).
|
| Los endpoints de sesión (/auth/session, /logout) viven en web —no en api—
| porque necesitan el stack de sesión y cookies para el puente Firebase→Laravel.
|
*/

Route::get('/', function () {
    // El UID de Firebase vive en la sesión. No usamos auth()->check() aquí
    // porque el middleware 'firebase' (que resuelve auth()->user()) no corre
    // en esta ruta; solo corre dentro del grupo protegido.
    return session()->has('firebase_uid')
        ? redirect()->route('dashboard')
        : redirect()->route('login');
})->name('home');

/* ── Autenticación ───────────────────────────────────────────────────────── */
Route::view('/login', 'auth.login')
    ->name('login');

Route::post('/auth/session', [FirebaseAuthController::class, 'session'])
    ->name('auth.session');

Route::post('/logout', [FirebaseAuthController::class, 'logout'])
    ->name('logout');

/* ── Zona protegida (médico Firebase resuelto) ──────────────────────────── */
Route::middleware('firebase')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Pacientes (páginas Blade; las mutaciones van por API)
    Route::get('/pacientes',                 [PacientePageController::class, 'index'])->name('pacientes.index');
    Route::get('/pacientes/create',          [PacientePageController::class, 'create'])->name('pacientes.create');
    Route::get('/pacientes/{paciente}',      [PacientePageController::class, 'show'])->name('pacientes.show');
    Route::get('/pacientes/{paciente}/edit', [PacientePageController::class, 'edit'])->name('pacientes.edit');

    // Historia clínica (wizard 1:1 con el paciente)
    Route::get('/pacientes/{paciente}/historia', [HistoriaClinicaPageController::class, 'edit'])->name('historias.edit');
    Route::get('/pacientes/{paciente}/historia/imprimir', [ImpresionController::class, 'historia'])->name('historias.imprimir');

    // Notas médicas (timeline + alta/edición por paciente)
    Route::get('/pacientes/{paciente}/notas',              [NotaMedicaPageController::class, 'index'])->name('notas.index');
    Route::get('/pacientes/{paciente}/notas/create',       [NotaMedicaPageController::class, 'create'])->name('notas.create');
    Route::get('/pacientes/{paciente}/notas/{nota}',       [NotaMedicaPageController::class, 'show'])->name('notas.show');
    Route::get('/pacientes/{paciente}/notas/{nota}/edit',  [NotaMedicaPageController::class, 'edit'])->name('notas.edit');
    Route::get('/pacientes/{paciente}/notas/{nota}/imprimir', [ImpresionController::class, 'nota'])->name('notas.imprimir');

    // Documentos del paciente
    Route::get('/pacientes/{paciente}/documentos',  [DocumentoController::class, 'index'])->name('documentos.index');
    Route::post('/pacientes/{paciente}/documentos', [DocumentoController::class, 'store'])->name('documentos.store');
    Route::post('/pacientes/{paciente}/documentos/{documento}/eliminar', [DocumentoController::class, 'destroy'])->name('documentos.destroy');

    // Catálogo de medicamentos (lectura para todos; gestión solo admin más abajo)
    Route::get('/medicamentos', [MedicamentoPageController::class, 'index'])->name('medicamentos.index');
});

/* ── Zona admin (rol_sistema = admin) ───────────────────────────────────── */
Route::middleware('firebase:admin')->group(function () {
    Route::get('/auditoria', [AuditoriaController::class, 'index'])->name('auditoria.index');

    // Establecimientos (gestión completa; solo admin)
    Route::get('/establecimientos', [EstablecimientoPageController::class, 'index'])->name('establecimientos.index');
});
