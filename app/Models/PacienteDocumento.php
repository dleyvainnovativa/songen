<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * Documento adjunto de un paciente.
 *
 * El archivo físico vive en el disk 'public' bajo la ruta guardada en `ruta`.
 * Expone helpers para URL pública, tamaño legible e icono según el mime.
 */
class PacienteDocumento extends Model
{
    protected $table = 'paciente_documentos';
    protected $primaryKey = 'id_documento';

    protected $fillable = [
        'id_paciente', 'id_medico', 'titulo', 'categoria',
        'nombre_archivo', 'ruta', 'mime', 'tamano_bytes', 'notas',
    ];

    protected $casts = [
        'tamano_bytes' => 'integer',
    ];

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class, 'id_paciente', 'id_paciente');
    }

    public function medico(): BelongsTo
    {
        return $this->belongsTo(PersonalMedico::class, 'id_medico', 'id_medico');
    }

    /* ── Helpers ─────────────────────────────────────────────────────────── */

    /** URL pública del archivo (requiere `php artisan storage:link`). */
    public function getUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->ruta);
    }

    /** Tamaño legible (KB / MB). */
    public function getTamanoLegibleAttribute(): string
    {
        $b = $this->tamano_bytes;
        if ($b >= 1048576) return round($b / 1048576, 1) . ' MB';
        if ($b >= 1024)    return round($b / 1024, 0) . ' KB';
        return $b . ' B';
    }

    /** Icono Font Awesome según el tipo de archivo. */
    public function getIconoAttribute(): string
    {
        return match (true) {
            str_contains($this->mime, 'pdf')                 => 'fa-file-pdf',
            str_contains($this->mime, 'image')               => 'fa-file-image',
            str_contains($this->mime, 'word'),
            str_contains($this->mime, 'document')            => 'fa-file-word',
            str_contains($this->mime, 'sheet'),
            str_contains($this->mime, 'excel')               => 'fa-file-excel',
            default                                          => 'fa-file',
        };
    }

    public function getEsImagenAttribute(): bool
    {
        return str_contains($this->mime, 'image');
    }
}
