<?php

namespace App\Services;

use App\Models\AuditoriaAcceso;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

/**
 * AuditoriaService — punto único de escritura de la bitácora de auditoría.
 *
 * Toda acción relevante sobre datos clínicos pasa por aquí. El observer de
 * modelos (AuditableObserver) dispara CREACION/MODIFICACION/ELIMINACION de
 * forma automática; CONSULTA e IMPRESION se registran explícitamente desde
 * los controladores cuando alguien ve o imprime un expediente.
 *
 * Diseño deliberado: una sola tabla (auditoria_accesos), un solo método
 * `registrar()`. Sin diffs JSON por ahora — mantenemos lo que ya existe y
 * solo lo conectamos. Resuelve el médico y la IP automáticamente.
 */
class AuditoriaService
{
    public const CONSULTA     = 'CONSULTA';
    public const CREACION     = 'CREACION';
    public const MODIFICACION = 'MODIFICACION';
    public const ELIMINACION  = 'ELIMINACION';
    public const IMPRESION    = 'IMPRESION';

    /**
     * Registra una entrada de auditoría.
     *
     * @param string      $accion        Una de las constantes de la clase.
     * @param string|null $tabla         Tabla afectada (p. ej. 'pacientes').
     * @param int|null    $idRegistro    PK del registro afectado.
     * @param string|null $descripcion   Texto legible para el panel de auditoría.
     * @param int|null    $idPaciente    Paciente relacionado, si aplica.
     */
    public function registrar(
        string $accion,
        ?string $tabla = null,
        ?int $idRegistro = null,
        ?string $descripcion = null,
        ?int $idPaciente = null
    ): AuditoriaAcceso {
        return AuditoriaAcceso::create([
            'id_medico'      => $this->medicoActual(),
            'id_paciente'    => $idPaciente,
            'accion'         => $accion,
            'tabla_afectada' => $tabla,
            'id_registro'    => $idRegistro,
            'descripcion'    => $descripcion,
            'ip_origen'      => Request::ip(),
            'fecha_hora'     => now(),
        ]);
    }

    /* ── Atajos semánticos ───────────────────────────────────────────────── */

    public function consulta(string $tabla, int $id, ?string $desc = null, ?int $idPaciente = null): AuditoriaAcceso
    {
        return $this->registrar(self::CONSULTA, $tabla, $id, $desc, $idPaciente);
    }

    public function impresion(string $tabla, int $id, ?string $desc = null, ?int $idPaciente = null): AuditoriaAcceso
    {
        return $this->registrar(self::IMPRESION, $tabla, $id, $desc, $idPaciente);
    }

    /**
     * Resuelve el id_medico autenticado. Null si no hay sesión resuelta
     * (p. ej. eventos de sistema), lo cual la columna permite.
     */
    protected function medicoActual(): ?int
    {
        $user = Auth::user();
        return $user?->getAuthIdentifier();
    }
}
