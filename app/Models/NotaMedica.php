<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Nota médica (formato SOAP) + signos vitales.
 *
 * Una vez `firmada`, la nota queda bloqueada para edición (regla de Fase 4).
 * Si el tipo de nota es quirúrgico, se adjunta la extensión notaQuirurgica.
 */
class NotaMedica extends Model
{
    protected $table = 'notas_medicas';
    protected $primaryKey = 'id_nota';

    protected $fillable = [
        'id_paciente', 'id_medico', 'id_tipo_nota', 'fecha_hora',
        'subjetivo', 'objetivo', 'analisis', 'plan',
        'presion_arterial', 'frecuencia_cardiaca', 'frecuencia_respiratoria',
        'temperatura_c', 'saturacion_o2', 'peso_kg', 'firmada', 'fecha_firma',
    ];

    protected $casts = [
        'fecha_hora'    => 'datetime',
        'fecha_firma'   => 'datetime',
        'firmada'       => 'boolean',
        'temperatura_c' => 'decimal:1',
        'peso_kg'       => 'decimal:2',
    ];

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class, 'id_paciente', 'id_paciente');
    }

    public function medico(): BelongsTo
    {
        return $this->belongsTo(PersonalMedico::class, 'id_medico', 'id_medico');
    }

    public function tipoNota(): BelongsTo
    {
        return $this->belongsTo(CatTipoNota::class, 'id_tipo_nota', 'id_tipo_nota');
    }

    public function notaQuirurgica(): HasOne
    {
        return $this->hasOne(NotaQuirurgica::class, 'id_nota', 'id_nota');
    }

    public function getBloqueadaAttribute(): bool
    {
        return $this->firmada;
    }
}
