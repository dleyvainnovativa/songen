<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validación de la Historia Clínica (crear/editar comparten reglas).
 *
 * Los campos del padre (historias_clinicas) se validan estáticamente. Los
 * campos del subtipo se generan dinámicamente desde config/hc_subtipos.php
 * según el tipo del paciente de la ruta, de modo que cada tipo solo valida
 * SUS campos y agregar/quitar uno no toca este archivo.
 */
class SaveHistoriaClinicaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // cubierto por middleware 'firebase'
    }

    public function rules(): array
    {
        return array_merge($this->reglasPadre(), $this->reglasSubtipo());
    }

    /** Campos del padre: antecedentes, padecimiento, exploración, dx/plan. */
    private function reglasPadre(): array
    {
        return [
            'fecha_elaboracion'        => ['nullable', 'date'],

            // Antecedentes heredofamiliares (bool)
            'ant_hf_diabetes'          => ['boolean'],
            'ant_hf_hipertension'      => ['boolean'],
            'ant_hf_cardiopatia'       => ['boolean'],
            'ant_hf_cancer'            => ['boolean'],
            'ant_hf_otros'             => ['nullable', 'string', 'max:255'],

            // No patológicos
            'tabaquismo'               => ['boolean'],
            'tabaquismo_detalle'       => ['nullable', 'string', 'max:255'],
            'alcoholismo'              => ['boolean'],
            'alcoholismo_detalle'      => ['nullable', 'string', 'max:255'],
            'toxicomanias'             => ['boolean'],
            'toxicomanias_detalle'     => ['nullable', 'string', 'max:255'],
            'actividad_fisica'         => ['nullable', 'string', 'max:255'],
            'dieta'                    => ['nullable', 'string', 'max:255'],

            // Patológicos
            'enfermedades_previas'        => ['nullable', 'string'],
            'hospitalizaciones_previas'   => ['nullable', 'string'],
            'cirugias_previas'            => ['nullable', 'string'],
            'traumatismos_previos'        => ['nullable', 'string'],
            'transfusiones'               => ['boolean'],
            'transfusiones_detalle'       => ['nullable', 'string', 'max:255'],

            // Gineco-obstétricos (opcionales)
            'menarca'                  => ['nullable', 'integer', 'min:5', 'max:20'],
            'ciclos_menstruales'       => ['nullable', 'string', 'max:60'],
            'gestas'                   => ['nullable', 'integer', 'min:0', 'max:30'],
            'partos'                   => ['nullable', 'integer', 'min:0', 'max:30'],
            'cesareas'                 => ['nullable', 'integer', 'min:0', 'max:30'],
            'abortos'                  => ['nullable', 'integer', 'min:0', 'max:30'],
            'fecha_ultima_regla'       => ['nullable', 'date'],

            // Padecimiento actual
            'motivo_consulta'          => ['required', 'string'],
            'padecimiento_actual'      => ['nullable', 'string'],

            // Signos vitales / somatometría
            'peso_kg'                  => ['nullable', 'numeric', 'min:0', 'max:500'],
            'talla_cm'                 => ['nullable', 'numeric', 'min:0', 'max:300'],
            'presion_arterial'         => ['nullable', 'string', 'max:20'],
            'frecuencia_cardiaca'      => ['nullable', 'integer', 'min:0', 'max:300'],
            'frecuencia_respiratoria'  => ['nullable', 'integer', 'min:0', 'max:120'],
            'temperatura_c'            => ['nullable', 'numeric', 'min:25', 'max:45'],
            'saturacion_o2'            => ['nullable', 'integer', 'min:0', 'max:100'],
            'exploracion_fisica'       => ['nullable', 'string'],

            // Diagnóstico y plan
            'diagnostico_inicial'      => ['required', 'string'],
            'plan_manejo'              => ['nullable', 'string'],
            'pronostico'               => ['nullable', 'string'],
        ];
    }

    /** Reglas generadas desde config según el tipo del paciente de la ruta. */
    private function reglasSubtipo(): array
    {
        $paciente = $this->route('paciente');
        $cfg = config("hc_subtipos.{$paciente->tipo_paciente}");
        if (! $cfg) {
            return [];
        }

        $reglas = [];
        foreach ($cfg['campos'] as $campo => $meta) {
            $key = "subtipo.{$campo}";
            $reglas[$key] = match ($meta['tipo']) {
                'bool'   => ['nullable', 'boolean'],
                'number' => array_filter([
                    'nullable', 'integer',
                    isset($meta['min']) ? 'min:'.$meta['min'] : null,
                    isset($meta['max']) ? 'max:'.$meta['max'] : null,
                ]),
                'scale'  => array_filter([
                    'nullable', 'integer',
                    isset($meta['min']) ? 'min:'.$meta['min'] : null,
                    isset($meta['max']) ? 'max:'.$meta['max'] : null,
                ]),
                'select' => ['nullable', isset($meta['opciones']) ? 'in:'.implode(',', array_keys($meta['opciones'])) : 'string'],
                'textarea', 'text' => ['nullable', 'string', 'max:1000'],
                default  => ['nullable', 'string'],
            };
        }
        // El grupo subtipo es opcional en su conjunto.
        $reglas['subtipo'] = ['nullable', 'array'];
        return $reglas;
    }

    public function messages(): array
    {
        return [
            'motivo_consulta.required'     => 'El motivo de consulta es obligatorio.',
            'diagnostico_inicial.required' => 'El diagnóstico inicial es obligatorio.',
        ];
    }
}
