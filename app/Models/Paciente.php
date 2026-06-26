<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Paciente.
 *
 * El campo `tipo_paciente` (Quirúrgico|Neurológico|Geriátrico) se fija en la
 * creación y determina la extensión de la Historia Clínica (ver config/hc_subtipos).
 *
 * @property int    $id_paciente
 * @property string $tipo_paciente
 */
class Paciente extends Model
{
    protected $table = 'pacientes';
    protected $primaryKey = 'id_paciente';

    protected $fillable = [
        'id_establecimiento', 'numero_expediente', 'nombre', 'primer_apellido',
        'segundo_apellido', 'fecha_nacimiento', 'sexo', 'curp', 'id_estado_civil',
        'id_escolaridad', 'ocupacion', 'religion', 'etnia', 'domicilio', 'colonia',
        'municipio', 'estado', 'cp', 'telefono', 'email', 'tipo_paciente',
        'id_tipo_sangre', 'alergias_conocidas', 'activo',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
        'activo'           => 'boolean',
    ];

    /* ── Relaciones ──────────────────────────────────────────────────────── */

    public function establecimiento(): BelongsTo
    {
        return $this->belongsTo(Establecimiento::class, 'id_establecimiento', 'id_establecimiento');
    }

    public function estadoCivil(): BelongsTo
    {
        return $this->belongsTo(CatEstadoCivil::class, 'id_estado_civil', 'id_estado_civil');
    }

    public function escolaridad(): BelongsTo
    {
        return $this->belongsTo(CatEscolaridad::class, 'id_escolaridad', 'id_escolaridad');
    }

    public function tipoSangre(): BelongsTo
    {
        return $this->belongsTo(CatTipoSangre::class, 'id_tipo_sangre', 'id_tipo_sangre');
    }

    /** HC es 1:1 con el paciente (UNIQUE en id_paciente). */
    public function historiaClinica(): HasOne
    {
        return $this->hasOne(HistoriaClinica::class, 'id_paciente', 'id_paciente');
    }

    public function notasMedicas(): HasMany
    {
        return $this->hasMany(NotaMedica::class, 'id_paciente', 'id_paciente');
    }

    public function contactosEmergencia(): HasMany
    {
        return $this->hasMany(PacienteContactoEmergencia::class, 'id_paciente', 'id_paciente');
    }

    public function medicamentos(): HasMany
    {
        return $this->hasMany(PacienteMedicamento::class, 'id_paciente', 'id_paciente');
    }

    public function documentos(): HasMany
    {
        return $this->hasMany(PacienteDocumento::class, 'id_paciente', 'id_paciente');
    }

    /* ── Accesores ───────────────────────────────────────────────────────── */

    public function getNombreCompletoAttribute(): string
    {
        return trim("{$this->nombre} {$this->primer_apellido} {$this->segundo_apellido}");
    }

    public function getEdadAttribute(): ?int
    {
        return $this->fecha_nacimiento?->age;
    }

    /** Metadata de UI/subtipo desde config/hc_subtipos.php */
    public function subtipoConfig(): ?array
    {
        return config("hc_subtipos.{$this->tipo_paciente}");
    }
}
