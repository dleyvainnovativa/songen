<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Registro de auditoría. Lo escribe AuditoriaService, no se crea a mano.
 * Sin timestamps de Laravel: usa su propia columna fecha_hora.
 */
class AuditoriaAcceso extends Model
{
    protected $table = 'auditoria_accesos';
    protected $primaryKey = 'id_auditoria';
    public $timestamps = false;

    protected $fillable = [
        'id_medico', 'id_paciente', 'accion', 'tabla_afectada',
        'id_registro', 'descripcion', 'ip_origen', 'fecha_hora',
    ];

    protected $casts = ['fecha_hora' => 'datetime'];

    public function medico(): BelongsTo
    {
        return $this->belongsTo(PersonalMedico::class, 'id_medico', 'id_medico');
    }

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class, 'id_paciente', 'id_paciente');
    }
}
