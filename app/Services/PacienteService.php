<?php

namespace App\Services;

use App\Models\Paciente;
use Illuminate\Support\Facades\DB;

/**
 * PacienteService — lógica de negocio de pacientes.
 *
 * Mantiene los controladores delgados: validación va en Form Requests, y la
 * orquestación (transacciones, sub-registros anidados, baja lógica) vive aquí.
 *
 * Los contactos de emergencia y medicamentos se manejan como sub-formularios
 * anidados: se reemplazan en bloque al guardar (estrategia simple y predecible
 * para shared hosting, sin diffing fila por fila).
 */
class PacienteService
{
    /**
     * Crea un paciente con sus contactos y medicamentos en una transacción.
     *
     * @param array $datos       Campos del paciente ya validados.
     * @param array $contactos   Lista de contactos de emergencia.
     * @param array $medicamentos Lista de medicamentos del paciente.
     */
    public function crear(array $datos, array $contactos = [], array $medicamentos = []): Paciente
    {
        return DB::transaction(function () use ($datos, $contactos, $medicamentos) {
            $paciente = Paciente::create($datos);
            $this->sincronizarContactos($paciente, $contactos);
            $this->sincronizarMedicamentos($paciente, $medicamentos);
            return $paciente->fresh(['contactosEmergencia', 'medicamentos']);
        });
    }

    /**
     * Actualiza un paciente y reemplaza sus sub-registros.
     * Nota: tipo_paciente NO se cambia aquí una vez creada la historia; ver guardia
     * en el controlador. (Cambiarlo invalidaría la extensión de HC ya existente.)
     */
    public function actualizar(Paciente $paciente, array $datos, array $contactos = [], array $medicamentos = []): Paciente
    {
        return DB::transaction(function () use ($paciente, $datos, $contactos, $medicamentos) {
            $paciente->update($datos);
            $this->sincronizarContactos($paciente, $contactos);
            $this->sincronizarMedicamentos($paciente, $medicamentos);
            return $paciente->fresh(['contactosEmergencia', 'medicamentos']);
        });
    }

    /** Baja lógica: marca activo = false. No borra datos clínicos. */
    public function desactivar(Paciente $paciente): Paciente
    {
        $paciente->update(['activo' => false]);
        return $paciente;
    }

    /** Reactiva un paciente dado de baja. */
    public function reactivar(Paciente $paciente): Paciente
    {
        $paciente->update(['activo' => true]);
        return $paciente;
    }

    /* ── Sub-registros anidados ──────────────────────────────────────────── */

    private function sincronizarContactos(Paciente $paciente, array $contactos): void
    {
        $paciente->contactosEmergencia()->delete();
        foreach ($contactos as $c) {
            if (empty($c['nombre_completo']) || empty($c['telefono'])) {
                continue; // omite filas vacías del formulario
            }
            $paciente->contactosEmergencia()->create([
                'nombre_completo' => $c['nombre_completo'],
                'parentesco'      => $c['parentesco'] ?? null,
                'telefono'        => $c['telefono'],
                'telefono_alt'    => $c['telefono_alt'] ?? null,
            ]);
        }
    }

    private function sincronizarMedicamentos(Paciente $paciente, array $medicamentos): void
    {
        $paciente->medicamentos()->delete();
        foreach ($medicamentos as $m) {
            if (empty($m['id_medicamento']) || empty($m['dosis'])) {
                continue;
            }
            $paciente->medicamentos()->create([
                'id_medicamento' => $m['id_medicamento'],
                'dosis'          => $m['dosis'],
                'frecuencia'     => $m['frecuencia'] ?? '',
                'fecha_inicio'   => $m['fecha_inicio'] ?? now()->toDateString(),
                'fecha_fin'      => $m['fecha_fin'] ?? null,
                'prescrito_por'  => $m['prescrito_por'] ?? null,
                'activo'         => $m['activo'] ?? true,
                'observaciones'  => $m['observaciones'] ?? null,
            ]);
        }
    }
}
