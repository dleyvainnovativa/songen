<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validación de edición de personal médico. Solo admin.
 *
 * No incluye creación de credenciales (eso se hace por separado al crear o
 * mediante el comando de vinculación). La guardia del último admin vive en el
 * servicio, no aquí.
 */
class UpdatePersonalMedicoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_establecimiento' => ['required', 'integer', 'exists:establecimientos,id_establecimiento'],
            'nombre'             => ['required', 'string', 'max:80'],
            'primer_apellido'    => ['required', 'string', 'max:60'],
            'segundo_apellido'   => ['nullable', 'string', 'max:60'],
            'cedula_profesional' => ['required', 'string', 'max:30'],
            'cedula_especialidad' => ['nullable', 'string', 'max:30'],
            'id_especialidad'    => ['nullable', 'integer', 'exists:cat_especialidades,id_especialidad'],
            'rol'                => ['nullable', Rule::in(config('roles_clinicos'))],
            'rol_sistema'        => ['required', Rule::in(['admin', 'medico'])],
            'email'              => ['nullable', 'email', 'max:120'],
            'telefono'           => ['nullable', 'string', 'max:15'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required'             => 'El nombre es obligatorio.',
            'primer_apellido.required'    => 'El primer apellido es obligatorio.',
            'cedula_profesional.required' => 'La cédula profesional es obligatoria.',
            'id_establecimiento.required' => 'Selecciona el establecimiento.',
            'rol_sistema.required'        => 'Selecciona el rol del sistema.',
        ];
    }
}
