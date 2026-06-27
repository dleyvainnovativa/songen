<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validación de alta de personal médico. Solo admin (ruta firebase:admin).
 *
 * Campos NOT NULL del esquema: id_establecimiento, nombre, primer_apellido,
 * cedula_profesional. Las credenciales (email + password) son opcionales: si
 * vienen, el servicio intenta crear/enlazar la cuenta Firebase.
 */
class StorePersonalMedicoRequest extends FormRequest
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

            // Credenciales Firebase (opcionales)
            'crear_acceso'       => ['nullable', 'boolean'],
            'password'           => ['nullable', 'required_if:crear_acceso,1', 'string', 'min:6'],
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
            'password.required_if'        => 'Para crear el acceso, escribe una contraseña (mín. 6 caracteres).',
            'password.min'                => 'La contraseña debe tener al menos 6 caracteres.',
        ];
    }
}
