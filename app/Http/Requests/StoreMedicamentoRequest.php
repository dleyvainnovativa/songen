<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validación de alta de medicamento. Solo admin (la ruta usa firebase:admin).
 */
class StoreMedicamentoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre_generico'    => ['required', 'string', 'max:150'],
            'nombre_comercial'   => ['nullable', 'string', 'max:150'],
            'forma_farmaceutica' => ['nullable', 'string', 'max:60'],
            'concentracion'      => ['nullable', 'string', 'max:40'],
            'via_administracion' => ['nullable', 'string', 'max:40'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre_generico.required' => 'El nombre genérico es obligatorio.',
        ];
    }
}
