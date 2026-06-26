<?php

namespace App\Services;

use App\Models\NotaMedica;
use App\Models\Paciente;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * NotaMedicaService — lógica de notas médicas (formato SOAP).
 *
 * Reglas centrales:
 *   - Una nota se puede editar mientras NO esté firmada.
 *   - Al firmar (`firmar`), queda bloqueada: cualquier intento posterior de
 *     editar o re-firmar se rechaza.
 *   - Si el tipo de nota es quirúrgico, se adjunta/actualiza la extensión
 *     notas_quirurgicas en la misma transacción.
 *
 * La firma es un candado lógico (firmada = true + fecha_firma), no una firma
 * criptográfica; cumple el requisito de inmutabilidad del expediente.
 */
class NotaMedicaService
{
    /** Tipos de nota (cat_tipos_nota) que activan la extensión quirúrgica. */
    private const TIPOS_QUIRURGICOS = ['Preoperatoria', 'Postoperatoria'];

    public function crear(Paciente $paciente, array $datos, int $idMedico, array $quirurgica = []): NotaMedica
    {
        return DB::transaction(function () use ($paciente, $datos, $idMedico, $quirurgica) {
            $nota = NotaMedica::create(array_merge($datos, [
                'id_paciente' => $paciente->id_paciente,
                'id_medico'   => $idMedico,
                'fecha_hora'  => $datos['fecha_hora'] ?? now(),
                'firmada'     => false,
            ]));

            $this->sincronizarQuirurgica($nota, $quirurgica);
            return $nota->fresh();
        });
    }

    /**
     * Actualiza una nota. Rechaza si ya está firmada (candado).
     *
     * @throws ValidationException
     */
    public function actualizar(NotaMedica $nota, array $datos, array $quirurgica = []): NotaMedica
    {
        $this->garantizarEditable($nota);

        return DB::transaction(function () use ($nota, $datos, $quirurgica) {
            $nota->update($datos);
            $this->sincronizarQuirurgica($nota, $quirurgica);
            return $nota->fresh();
        });
    }

    /**
     * Firma la nota: la bloquea de forma permanente.
     *
     * @throws ValidationException si ya estaba firmada.
     */
    public function firmar(NotaMedica $nota): NotaMedica
    {
        if ($nota->firmada) {
            throw ValidationException::withMessages([
                'firma' => 'La nota ya está firmada y no puede volver a firmarse.',
            ]);
        }

        $nota->update([
            'firmada'     => true,
            'fecha_firma' => now(),
        ]);

        return $nota->fresh();
    }

    /** Lanza excepción si la nota está firmada (no editable). */
    private function garantizarEditable(NotaMedica $nota): void
    {
        if ($nota->firmada) {
            throw ValidationException::withMessages([
                'nota' => 'Esta nota está firmada y no puede modificarse.',
            ]);
        }
    }

    /** ¿El tipo de nota requiere extensión quirúrgica? */
    public function requiereQuirurgica(NotaMedica $nota): bool
    {
        return in_array($nota->tipoNota?->descripcion, self::TIPOS_QUIRURGICOS, true);
    }

    /** Crea/actualiza/elimina la extensión quirúrgica según el tipo de nota. */
    private function sincronizarQuirurgica(NotaMedica $nota, array $datos): void
    {
        if (! $this->requiereQuirurgica($nota)) {
            // Si dejó de ser quirúrgica, limpia cualquier extensión previa.
            $nota->notaQuirurgica()->delete();
            return;
        }

        $nota->notaQuirurgica()->updateOrCreate(
            ['id_nota' => $nota->id_nota],
            array_merge($datos, [
                'id_nota'     => $nota->id_nota,
                'id_paciente' => $nota->id_paciente,
            ])
        );
    }
}
