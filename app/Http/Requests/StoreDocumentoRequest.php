<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validación de subida de documento.
 *
 * Tipos permitidos: PDF, imágenes (jpg/png) y Office (docx/xlsx). Máx 10 MB.
 */
class StoreDocumentoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // cualquier médico (middleware 'firebase')
    }

    public function rules(): array
    {
        return [
            'titulo'    => ['required', 'string', 'max:150'],
            'categoria' => ['nullable', Rule::in(['Estudio', 'Receta', 'Consentimiento', 'Laboratorio', 'Imagenología', 'Otro'])],
            'notas'     => ['nullable', 'string', 'max:500'],
            'archivo'   => [
                'required', 'file', 'max:10240', // 10 MB en KB
                'mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'archivo.required' => 'Selecciona un archivo.',
            'archivo.max'      => 'El archivo no debe superar 10 MB.',
            'archivo.mimes'    => 'Formato no permitido. Usa PDF, imagen (JPG/PNG) u Office (Word/Excel).',
            'titulo.required'  => 'Escribe un título para el documento.',
        ];
    }
}
