<?php

namespace App\Services;

use App\Models\HistoriaClinica;
use App\Models\Paciente;
use Illuminate\Support\Facades\DB;

/**
 * HistoriaClinicaService — orquesta el guardado de la Historia Clínica.
 *
 * La HC es 1:1 con el paciente. Su extensión (hc_quirurgicas / hc_neurologicas /
 * hc_geriatricas) depende del tipo_paciente y se resuelve desde
 * config/hc_subtipos.php — única fuente de verdad. Así este servicio no conoce
 * los campos de cada subtipo; solo sabe a qué tabla/modelo escribir.
 *
 * Guarda padre + hijo en una sola transacción (updateOrCreate en ambos), de modo
 * que crear y editar comparten el mismo camino.
 */
class HistoriaClinicaService
{
    /**
     * Crea o actualiza la HC de un paciente junto con su extensión de subtipo.
     *
     * @param Paciente $paciente
     * @param array    $datosHc       Campos del padre (historias_clinicas) validados.
     * @param array    $datosSubtipo  Campos de la extensión (hc_*) validados.
     * @param int      $idMedico      Médico responsable (del usuario autenticado).
     */
    public function guardar(Paciente $paciente, array $datosHc, array $datosSubtipo, int $idMedico): HistoriaClinica
    {
        $cfg = config("hc_subtipos.{$paciente->tipo_paciente}");

        if (! $cfg) {
            throw new \RuntimeException("Tipo de paciente sin configuración de subtipo: {$paciente->tipo_paciente}");
        }

        return DB::transaction(function () use ($paciente, $datosHc, $datosSubtipo, $idMedico, $cfg) {
            // Calcula IMC si hay peso y talla.
            $datosHc['imc'] = $this->calcularImc(
                $datosHc['peso_kg'] ?? null,
                $datosHc['talla_cm'] ?? null
            );

            // 1. Padre: historias_clinicas (1:1 con paciente).
            $historia = HistoriaClinica::updateOrCreate(
                ['id_paciente' => $paciente->id_paciente],
                array_merge($datosHc, [
                    'id_medico_responsable' => $idMedico,
                    'fecha_elaboracion'     => $datosHc['fecha_elaboracion'] ?? now(),
                ])
            );

            // 2. Hijo: extensión del subtipo, resuelta por config.
            $modelo = $cfg['modelo'];
            $fk     = $cfg['fk'];

            $modelo::updateOrCreate(
                [$fk => $historia->id_historia],
                array_merge($this->limpiarSubtipo($datosSubtipo, $cfg), [
                    $fk => $historia->id_historia,
                ])
            );

            return $historia->fresh();
        });
    }

    /**
     * Filtra el payload del subtipo dejando solo los campos declarados en config,
     * y normaliza los bool ausentes a false (los checkboxes/bool-cards no envían
     * nada cuando están apagados).
     */
    private function limpiarSubtipo(array $datos, array $cfg): array
    {
        $limpio = [];
        foreach ($cfg['campos'] as $campo => $meta) {
            if ($meta['tipo'] === 'bool') {
                $limpio[$campo] = ! empty($datos[$campo]);
            } elseif (array_key_exists($campo, $datos)) {
                // Cadena vacía → null para no romper columnas numéricas/fecha.
                $limpio[$campo] = ($datos[$campo] === '') ? null : $datos[$campo];
            }
        }
        return $limpio;
    }

    /** IMC = peso(kg) / talla(m)². Devuelve null si faltan datos. */
    private function calcularImc($peso, $talla): ?float
    {
        $peso = (float) $peso;
        $talla = (float) $talla;
        if ($peso <= 0 || $talla <= 0) {
            return null;
        }
        $m = $talla / 100;
        return round($peso / ($m * $m), 2);
    }
}
