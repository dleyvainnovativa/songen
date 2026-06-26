<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validación de alta de paciente.
 *
 * tipo_paciente es obligatorio aquí porque determina toda la extensión clínica
 * posterior (config/hc_subtipos). CURP y número de expediente son únicos.
 */
class StorePacienteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // la autorización la cubre el middleware 'firebase'
    }

    public function rules(): array
    {
        return [
            'id_establecimiento' => ['required', 'integer', 'exists:establecimientos,id_establecimiento'],
            'numero_expediente'  => ['required', 'string', 'max:30', Rule::unique('pacientes', 'numero_expediente')],
            'nombre'             => ['required', 'string', 'max:80'],
            'primer_apellido'    => ['required', 'string', 'max:60'],
            'segundo_apellido'   => ['nullable', 'string', 'max:60'],
            'fecha_nacimiento'   => ['required', 'date', 'before_or_equal:today'],
            'sexo'               => ['required', Rule::in(['M', 'F', 'Indeterminado'])],
            'curp'               => ['nullable', 'string', 'size:18', Rule::unique('pacientes', 'curp')],
            'id_estado_civil'    => ['nullable', 'integer', 'exists:cat_estados_civiles,id_estado_civil'],
            'id_escolaridad'     => ['nullable', 'integer', 'exists:cat_escolaridades,id_escolaridad'],
            'ocupacion'          => ['nullable', 'string', 'max:80'],
            'religion'           => ['nullable', 'string', 'max:60'],
            'etnia'              => ['nullable', 'string', 'max:60'],
            'domicilio'          => ['nullable', 'string', 'max:255'],
            'colonia'            => ['nullable', 'string', 'max:100'],
            'municipio'          => ['nullable', 'string', 'max:100'],
            'estado'             => ['nullable', 'string', 'max:60'],
            'cp'                 => ['nullable', 'string', 'size:5'],
            'telefono'           => ['nullable', 'string', 'max:15'],
            'email'              => ['nullable', 'email', 'max:120'],
            'tipo_paciente'      => ['required', Rule::in(['Quirúrgico', 'Neurológico', 'Geriátrico'])],
            'id_tipo_sangre'     => ['nullable', 'integer', 'exists:cat_tipos_sangre,id_tipo_sangre'],
            'alergias_conocidas' => ['nullable', 'string'],

            // Sub-registros anidados (opcionales)
            'contactos'                    => ['nullable', 'array'],
            'contactos.*.nombre_completo'  => ['nullable', 'string', 'max:150'],
            'contactos.*.parentesco'       => ['nullable', 'string', 'max:40'],
            'contactos.*.telefono'         => ['nullable', 'string', 'max:15'],
            'contactos.*.telefono_alt'     => ['nullable', 'string', 'max:15'],

            'medicamentos'                  => ['nullable', 'array'],
            'medicamentos.*.id_medicamento' => ['nullable', 'integer', 'exists:medicamentos,id_medicamento'],
            'medicamentos.*.dosis'          => ['nullable', 'string', 'max:60'],
            'medicamentos.*.frecuencia'     => ['nullable', 'string', 'max:60'],
            'medicamentos.*.fecha_inicio'   => ['nullable', 'date'],
            'medicamentos.*.fecha_fin'      => ['nullable', 'date', 'after_or_equal:medicamentos.*.fecha_inicio'],
        ];
    }

    public function messages(): array
    {
        return [
            'numero_expediente.unique' => 'Ya existe un paciente con ese número de expediente.',
            'curp.unique'              => 'Ya existe un paciente con esa CURP.',
            'curp.size'                => 'La CURP debe tener exactamente 18 caracteres.',
            'tipo_paciente.required'   => 'Selecciona el tipo de paciente.',
            'tipo_paciente.in'         => 'El tipo de paciente no es válido.',
        ];
    }

    public function attributes(): array
    {
        return [
            'numero_expediente' => 'número de expediente',
            'fecha_nacimiento'  => 'fecha de nacimiento',
            'tipo_paciente'     => 'tipo de paciente',
        ];
    }
}
