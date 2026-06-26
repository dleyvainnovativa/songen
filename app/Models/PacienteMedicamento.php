<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PacienteMedicamento extends Model
{
    protected $table = 'paciente_medicamentos';
    protected $primaryKey = 'id_pm';

    protected $fillable = [
        'id_paciente', 'id_medicamento', 'dosis', 'frecuencia', 'fecha_inicio',
        'fecha_fin', 'prescrito_por', 'activo', 'observaciones',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin'    => 'date',
        'activo'       => 'boolean',
    ];

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class, 'id_paciente', 'id_paciente');
    }

    public function medicamento(): BelongsTo
    {
        return $this->belongsTo(Medicamento::class, 'id_medicamento', 'id_medicamento');
    }

    public function prescriptor(): BelongsTo
    {
        return $this->belongsTo(PersonalMedico::class, 'prescrito_por', 'id_medico');
    }
}
