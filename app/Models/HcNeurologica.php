<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Extensión neurológica de la Historia Clínica (1:1 con historias_clinicas). */
class HcNeurologica extends Model
{
    protected $table = 'hc_neurologicas';
    protected $primaryKey = 'id_hc_neurologica';

    protected $fillable = [
        'id_historia', 'escala_glasgow', 'estado_mental', 'lenguaje', 'marcha',
        'reflejos', 'pares_craneales', 'fuerza_muscular', 'sensibilidad',
        'coordinacion', 'epilepsia', 'epilepsia_detalle', 'deterioro_cognitivo',
        'deterioro_escala', 'deterioro_puntaje', 'cefalea_cronica', 'ictus_previo',
        'ictus_detalle', 'observaciones',
    ];

    protected $casts = [
        'epilepsia'           => 'boolean',
        'deterioro_cognitivo' => 'boolean',
        'cefalea_cronica'     => 'boolean',
        'ictus_previo'        => 'boolean',
    ];

    public function historia(): BelongsTo
    {
        return $this->belongsTo(HistoriaClinica::class, 'id_historia', 'id_historia');
    }
}
