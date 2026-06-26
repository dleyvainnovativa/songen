<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validación de notas médicas (crear/editar comparten reglas).
 *
 * Campos SOAP + signos vitales del padre. Los campos de la extensión
 * quirúrgica son opcionales y solo se persisten si el tipo de nota lo amerita
 * (eso lo decide el servicio, no la validación).
 */
class SaveNotaMedicaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_tipo_nota'            => ['required', 'integer', 'exists:cat_tipos_nota,id_tipo_nota'],
            'fecha_hora'              => ['nullable', 'date'],

            // SOAP
            'subjetivo'               => ['nullable', 'string'],
            'objetivo'                => ['nullable', 'string'],
            'analisis'                => ['required', 'string'],
            'plan'                    => ['nullable', 'string'],

            // Signos vitales
            'presion_arterial'        => ['nullable', 'string', 'max:20'],
            'frecuencia_cardiaca'     => ['nullable', 'integer', 'min:0', 'max:300'],
            'frecuencia_respiratoria' => ['nullable', 'integer', 'min:0', 'max:120'],
            'temperatura_c'           => ['nullable', 'numeric', 'min:25', 'max:45'],
            'saturacion_o2'           => ['nullable', 'integer', 'min:0', 'max:100'],
            'peso_kg'                 => ['nullable', 'numeric', 'min:0', 'max:500'],

            // Extensión quirúrgica (opcional)
            'quirurgica'                              => ['nullable', 'array'],
            'quirurgica.id_cirujano'                  => ['nullable', 'integer', 'exists:personal_medico,id_medico'],
            'quirurgica.id_anestesiologo'             => ['nullable', 'integer', 'exists:personal_medico,id_medico'],
            'quirurgica.fecha_hora_inicio'            => ['nullable', 'date'],
            'quirurgica.fecha_hora_fin'               => ['nullable', 'date', 'after_or_equal:quirurgica.fecha_hora_inicio'],
            'quirurgica.tipo_anestesia'               => ['nullable', 'string', 'max:80'],
            'quirurgica.diagnostico_preoperatorio'    => ['nullable', 'string'],
            'quirurgica.diagnostico_postoperatorio'   => ['nullable', 'string'],
            'quirurgica.procedimiento_realizado'      => ['nullable', 'string'],
            'quirurgica.hallazgos'                    => ['nullable', 'string'],
            'quirurgica.tecnica'                      => ['nullable', 'string'],
            'quirurgica.material_implantado'          => ['nullable', 'string'],
            'quirurgica.complicaciones'               => ['nullable', 'string'],
            'quirurgica.sangrado_ml'                  => ['nullable', 'integer', 'min:0'],
            'quirurgica.diuresis_ml'                  => ['nullable', 'integer', 'min:0'],
            'quirurgica.estado_egreso'                => ['nullable', 'string', 'max:120'],
        ];
    }

    public function messages(): array
    {
        return [
            'analisis.required'     => 'El análisis (A) es obligatorio en la nota.',
            'id_tipo_nota.required' => 'Selecciona el tipo de nota.',
        ];
    }
}
