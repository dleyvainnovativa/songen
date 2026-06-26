<?php

namespace App\Services;

use App\Models\Paciente;
use App\Models\PacienteDocumento;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * DocumentoService — almacenamiento de documentos por paciente.
 *
 * Archivos en el disk 'public' bajo documentos/{id_paciente}/. El nombre en
 * disco se aleatoriza (evita colisiones y problemas de caracteres); el nombre
 * original se conserva en la columna nombre_archivo.
 */
class DocumentoService
{
    /** Sube un archivo y crea su registro. */
    public function subir(Paciente $paciente, UploadedFile $archivo, array $meta, ?int $idMedico): PacienteDocumento
    {
        $dir = "documentos/{$paciente->id_paciente}";

        // Nombre en disco: aleatorio + extensión original.
        $ext = strtolower($archivo->getClientOriginalExtension());
        $nombreDisco = Str::uuid() . ($ext ? ".{$ext}" : '');

        $ruta = $archivo->storeAs($dir, $nombreDisco, 'public');

        return $paciente->documentos()->create([
            'id_medico'      => $idMedico,
            'titulo'         => $meta['titulo'],
            'categoria'      => $meta['categoria'] ?? null,
            'nombre_archivo' => $archivo->getClientOriginalName(),
            'ruta'           => $ruta,
            'mime'           => $archivo->getClientMimeType(),
            'tamano_bytes'   => $archivo->getSize(),
            'notas'          => $meta['notas'] ?? null,
        ]);
    }

    /** Elimina el registro y su archivo físico. */
    public function eliminar(PacienteDocumento $documento): void
    {
        Storage::disk('public')->delete($documento->ruta);
        $documento->delete();
    }
}
