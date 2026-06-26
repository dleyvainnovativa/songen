<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Historia Clínica — 1:1 con el paciente.
 *
 * Tiene una extensión 1:1 según el tipo de paciente:
 *   Quirúrgico  → hcQuirurgica
 *   Neurológico → hcNeurologica
 *   Geriátrico  → hcGeriatrica
 *
 * Usa subtipo() para obtener la extensión correcta sin saber el tipo de antemano.
 */
class HistoriaClinica extends Model
{
    protected $table = 'historias_clinicas';
    protected $primaryKey = 'id_historia';

    protected $fillable = [
        'id_paciente', 'id_medico_responsable', 'fecha_elaboracion',
        'ant_hf_diabetes', 'ant_hf_hipertension', 'ant_hf_cardiopatia',
        'ant_hf_cancer', 'ant_hf_otros',
        'tabaquismo', 'tabaquismo_detalle', 'alcoholismo', 'alcoholismo_detalle',
        'toxicomanias', 'toxicomanias_detalle', 'actividad_fisica', 'dieta',
        'enfermedades_previas', 'hospitalizaciones_previas', 'cirugias_previas',
        'traumatismos_previos', 'transfusiones', 'transfusiones_detalle',
        'menarca', 'ciclos_menstruales', 'gestas', 'partos', 'cesareas', 'abortos',
        'fecha_ultima_regla', 'motivo_consulta', 'padecimiento_actual',
        'peso_kg', 'talla_cm', 'imc', 'presion_arterial', 'frecuencia_cardiaca',
        'frecuencia_respiratoria', 'temperatura_c', 'saturacion_o2',
        'exploracion_fisica', 'diagnostico_inicial', 'plan_manejo', 'pronostico',
    ];

    protected $casts = [
        'fecha_elaboracion'   => 'datetime',
        'fecha_ultima_regla'  => 'date',
        'ant_hf_diabetes'     => 'boolean',
        'ant_hf_hipertension' => 'boolean',
        'ant_hf_cardiopatia'  => 'boolean',
        'ant_hf_cancer'       => 'boolean',
        'tabaquismo'          => 'boolean',
        'alcoholismo'         => 'boolean',
        'toxicomanias'        => 'boolean',
        'transfusiones'       => 'boolean',
        'peso_kg'             => 'decimal:2',
        'talla_cm'            => 'decimal:1',
        'imc'                 => 'decimal:2',
        'temperatura_c'       => 'decimal:1',
    ];

    /* ── Relaciones ──────────────────────────────────────────────────────── */

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class, 'id_paciente', 'id_paciente');
    }

    public function medicoResponsable(): BelongsTo
    {
        return $this->belongsTo(PersonalMedico::class, 'id_medico_responsable', 'id_medico');
    }

    public function hcQuirurgica(): HasOne
    {
        return $this->hasOne(HcQuirurgica::class, 'id_historia', 'id_historia');
    }

    public function hcNeurologica(): HasOne
    {
        return $this->hasOne(HcNeurologica::class, 'id_historia', 'id_historia');
    }

    public function hcGeriatrica(): HasOne
    {
        return $this->hasOne(HcGeriatrica::class, 'id_historia', 'id_historia');
    }

    /* ── Resolver de subtipo ─────────────────────────────────────────────── */

    /**
     * Devuelve la extensión 1:1 correcta según el tipo del paciente,
     * sin que el llamador tenga que saber cuál es.
     */
    public function subtipo(): ?Model
    {
        return match ($this->paciente?->tipo_paciente) {
            'Quirúrgico'  => $this->hcQuirurgica,
            'Neurológico' => $this->hcNeurologica,
            'Geriátrico'  => $this->hcGeriatrica,
            default       => null,
        };
    }

    /** Nombre de la relación de subtipo para eager-loading dinámico. */
    public static function relacionSubtipo(string $tipoPaciente): ?string
    {
        return match ($tipoPaciente) {
            'Quirúrgico'  => 'hcQuirurgica',
            'Neurológico' => 'hcNeurologica',
            'Geriátrico'  => 'hcGeriatrica',
            default       => null,
        };
    }
}
