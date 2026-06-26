<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Extensión quirúrgica de una nota médica (1:1 con notas_medicas). */
class NotaQuirurgica extends Model
{
    protected $table = 'notas_quirurgicas';
    protected $primaryKey = 'id_nota_qx';

    protected $fillable = [
        'id_nota', 'id_paciente', 'id_cirujano', 'id_anestesiologo',
        'fecha_hora_inicio', 'fecha_hora_fin', 'tipo_anestesia',
        'diagnostico_preoperatorio', 'diagnostico_postoperatorio',
        'procedimiento_realizado', 'hallazgos', 'tecnica', 'material_implantado',
        'complicaciones', 'sangrado_ml', 'diuresis_ml', 'estado_egreso',
    ];

    protected $casts = [
        'fecha_hora_inicio' => 'datetime',
        'fecha_hora_fin'    => 'datetime',
    ];

    public function nota(): BelongsTo
    {
        return $this->belongsTo(NotaMedica::class, 'id_nota', 'id_nota');
    }

    public function cirujano(): BelongsTo
    {
        return $this->belongsTo(PersonalMedico::class, 'id_cirujano', 'id_medico');
    }

    public function anestesiologo(): BelongsTo
    {
        return $this->belongsTo(PersonalMedico::class, 'id_anestesiologo', 'id_medico');
    }
}
