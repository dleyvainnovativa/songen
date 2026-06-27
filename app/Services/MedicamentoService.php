<?php

namespace App\Services;

use App\Models\Medicamento;

/**
 * MedicamentoService — gestión del catálogo de medicamentos.
 *
 * CRUD simple. No se borran físicamente: se archivan (activo = false) porque
 * pueden estar referenciados en paciente_medicamentos. Mantenerlos evita
 * romper esas referencias históricas.
 */
class MedicamentoService
{
    public function crear(array $datos): Medicamento
    {
        return Medicamento::create(array_merge($datos, [
            'activo' => $datos['activo'] ?? true,
        ]));
    }

    public function actualizar(Medicamento $medicamento, array $datos): Medicamento
    {
        $medicamento->update($datos);
        return $medicamento;
    }

    /** Archiva (activo = false). No borra, por las referencias históricas. */
    public function archivar(Medicamento $medicamento): Medicamento
    {
        $medicamento->update(['activo' => false]);
        return $medicamento;
    }

    public function reactivar(Medicamento $medicamento): Medicamento
    {
        $medicamento->update(['activo' => true]);
        return $medicamento;
    }
}
