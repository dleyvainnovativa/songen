<?php

namespace App\Providers;

use App\Models\HcGeriatrica;
use App\Models\HcNeurologica;
use App\Models\HcQuirurgica;
use App\Models\HistoriaClinica;
use App\Models\NotaMedica;
use App\Models\NotaQuirurgica;
use App\Models\Paciente;
use App\Models\PacienteContactoEmergencia;
use App\Models\PacienteDocumento;
use App\Models\PacienteMedicamento;
use App\Observers\AuditableObserver;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use Kreait\Firebase\Contract\Auth as FirebaseAuth;
use Kreait\Firebase\Factory;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Modelos cuyos cambios se auditan automáticamente vía AuditableObserver.
     * (Catálogos y tablas de sistema quedan fuera a propósito.)
     */
    protected array $modelosAuditables = [
        Paciente::class,
        HistoriaClinica::class,
        HcQuirurgica::class,
        HcNeurologica::class,
        HcGeriatrica::class,
        NotaMedica::class,
        NotaQuirurgica::class,
        PacienteContactoEmergencia::class,
        PacienteMedicamento::class,
        PacienteDocumento::class,
    ];

    public function register(): void
    {
        // Cliente Firebase Auth (kreait) como singleton, desde credenciales del .env.
        $this->app->singleton(FirebaseAuth::class, function () {
            $factory = (new Factory)->withServiceAccount(
                base_path(config('firebase.credentials'))
            );
            return $factory->createAuth();
        });
    }

    public function boot(): void
    {
        // Usa la maquetación Bootstrap 5 para la paginación (no Tailwind, que
        // es el default de Laravel y rompe el estilo en este proyecto).
        Paginator::useBootstrapFive();

        foreach ($this->modelosAuditables as $modelo) {
            $modelo::observe(AuditableObserver::class);
        }
    }
}
