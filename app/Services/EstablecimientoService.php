<?php

namespace App\Services;

use App\Models\Establecimiento;
use Illuminate\Validation\ValidationException;

/**
 * EstablecimientoService — gestión de clínicas/establecimientos.
 *
 * Regla de borrado: no se puede eliminar un establecimiento que tenga pacientes
 * o personal médico ligados (rompería esas referencias). En ese caso se rechaza
 * con 422; el admin debe reasignar o conservar el registro.
 *
 * La tabla NO tiene timestamps (así viene del esquema), por eso el modelo
 * declara $timestamps = false.
 */
class EstablecimientoService
{
    public function crear(array $datos): Establecimiento
    {
        return Establecimiento::create(array_merge($datos, [
            'fecha_registro' => $datos['fecha_registro'] ?? now()->toDateString(),
        ]));
    }

    public function actualizar(Establecimiento $establecimiento, array $datos): Establecimiento
    {
        $establecimiento->update($datos);
        return $establecimiento;
    }

    /**
     * Elimina un establecimiento solo si no tiene pacientes ni personal ligados.
     *
     * @throws ValidationException
     */
    public function eliminar(Establecimiento $establecimiento): void
    {
        $pacientes = $establecimiento->pacientes()->count();
        $personal  = $establecimiento->personalMedico()->count();

        if ($pacientes > 0 || $personal > 0) {
            throw ValidationException::withMessages([
                'establecimiento' => "No se puede eliminar: tiene {$pacientes} paciente(s) y {$personal} miembro(s) de personal ligados.",
            ]);
        }

        $establecimiento->delete();
    }
}
