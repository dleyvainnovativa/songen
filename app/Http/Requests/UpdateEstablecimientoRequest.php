<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validación de edición de establecimiento. Solo admin (ruta firebase:admin).
 */
class UpdateEstablecimientoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre'             => ['required', 'string', 'max:150'],
            'razon_social'       => ['nullable', 'string', 'max:200'],
            'rfc'                => ['nullable', 'string', 'max:13'],
            'licencia_sanitaria' => ['nullable', 'string', 'max:60'],
            'domicilio'          => ['required', 'string', 'max:255'],
            'colonia'            => ['nullable', 'string', 'max:100'],
            'municipio'          => ['required', 'string', 'max:100'],
            'estado'             => ['required', 'string', 'max:60'],
            'cp'                 => ['nullable', 'string', 'size:5'],
            'telefono'           => ['nullable', 'string', 'max:15'],
            'email'              => ['nullable', 'email', 'max:120'],
            'nivel_atencion'     => ['nullable', 'integer', 'min:1', 'max:3'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required'    => 'El nombre del establecimiento es obligatorio.',
            'domicilio.required' => 'El domicilio es obligatorio.',
            'municipio.required' => 'El municipio es obligatorio.',
            'estado.required'    => 'El estado es obligatorio.',
        ];
    }
}
