<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Extensión quirúrgica de la Historia Clínica (1:1 con historias_clinicas). */
class HcQuirurgica extends Model
{
    protected $table = 'hc_quirurgicas';
    protected $primaryKey = 'id_hc_quirurgica';

    protected $fillable = [
        'id_historia', 'clasificacion_asa', 'riesgo_quirurgico', 'tipo_cirugia',
        'procedimiento_previsto', 'anestesia_tipo', 'ayuno_horas',
        'profilaxis_antibiotica', 'profilaxis_detalle', 'alergias_latex',
        'coagulopatia', 'coagulopatia_detalle', 'observaciones',
    ];

    protected $casts = [
        'profilaxis_antibiotica' => 'boolean',
        'alergias_latex'         => 'boolean',
        'coagulopatia'           => 'boolean',
    ];

    public function historia(): BelongsTo
    {
        return $this->belongsTo(HistoriaClinica::class, 'id_historia', 'id_historia');
    }
}
