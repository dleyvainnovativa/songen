<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Extensión geriátrica de la Historia Clínica (1:1 con historias_clinicas). */
class HcGeriatrica extends Model
{
    protected $table = 'hc_geriatricas';
    protected $primaryKey = 'id_hc_geriatrica';

    protected $fillable = [
        'id_historia', 'escala_barthel', 'escala_lawton', 'escala_tinetti_marcha',
        'escala_tinetti_equilibrio', 'riesgo_caidas', 'mini_mental_mmse',
        'escala_depresion_geriatrica', 'estado_nutricional', 'puntaje_nutricional',
        'numero_medicamentos', 'polifarmacia', 'red_apoyo_social', 'cuidador_primario',
        'vive_solo', 'tipo_vivienda', 'sindrome_fragilidad', 'sarcopenia',
        'incontinencia_urinaria', 'ulceras_presion', 'observaciones',
    ];

    protected $casts = [
        'polifarmacia'           => 'boolean',
        'vive_solo'              => 'boolean',
        'sindrome_fragilidad'    => 'boolean',
        'sarcopenia'             => 'boolean',
        'incontinencia_urinaria' => 'boolean',
        'ulceras_presion'        => 'boolean',
    ];

    public function historia(): BelongsTo
    {
        return $this->belongsTo(HistoriaClinica::class, 'id_historia', 'id_historia');
    }
}
