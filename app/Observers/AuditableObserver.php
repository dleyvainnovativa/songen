<?php

namespace App\Observers;

use App\Services\AuditoriaService;
use Illuminate\Database\Eloquent\Model;

/**
 * Observer genérico de auditoría.
 *
 * Se engancha a los modelos clínicos en AppServiceProvider. Dispara
 * automáticamente CREACION / MODIFICACION / ELIMINACION hacia AuditoriaService,
 * resolviendo tabla, PK y paciente relacionado sin código repetido en controladores.
 *
 * Las acciones CONSULTA e IMPRESION NO viven aquí (no son eventos de Eloquent);
 * se registran explícitamente desde los controladores.
 */
class AuditableObserver
{
    public function __construct(protected AuditoriaService $auditoria) {}

    public function created(Model $model): void
    {
        $this->log($model, AuditoriaService::CREACION, 'Registro creado');
    }

    public function updated(Model $model): void
    {
        $campos = implode(', ', array_keys($model->getChanges()));
        $this->log($model, AuditoriaService::MODIFICACION, "Campos: {$campos}");
    }

    public function deleted(Model $model): void
    {
        $this->log($model, AuditoriaService::ELIMINACION, 'Registro eliminado');
    }

    protected function log(Model $model, string $accion, string $desc): void
    {
        $this->auditoria->registrar(
            accion: $accion,
            tabla: $model->getTable(),
            idRegistro: (int) $model->getKey(),
            descripcion: $desc,
            idPaciente: $this->resolverPaciente($model),
        );
    }

    /** Intenta resolver a qué paciente pertenece el registro afectado. */
    protected function resolverPaciente(Model $model): ?int
    {
        if (isset($model->id_paciente)) {
            return (int) $model->id_paciente;
        }
        // Modelos colgados de la historia (hc_*) → vía relación historia.
        if (isset($model->id_historia) && method_exists($model, 'historia')) {
            return $model->historia?->id_paciente;
        }
        return null;
    }
}
