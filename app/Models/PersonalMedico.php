<?php

namespace App\Models;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Personal médico — es además el USUARIO autenticado del sistema.
 *
 * La sesión de Firebase se resuelve a uno de estos registros vía firebase_uid.
 * Implementa Authenticatable para integrarse con auth()->user() de Laravel.
 *
 * @property int         $id_medico
 * @property string|null $firebase_uid
 * @property string      $rol_sistema   admin|medico
 * @property bool        $activo
 */
class PersonalMedico extends Model implements Authenticatable
{
    protected $table = 'personal_medico';
    protected $primaryKey = 'id_medico';

    protected $fillable = [
        'id_establecimiento', 'firebase_uid', 'nombre', 'primer_apellido',
        'segundo_apellido', 'cedula_profesional', 'cedula_especialidad',
        'id_especialidad', 'rol', 'rol_sistema', 'email', 'telefono',
        'activo', 'ultimo_acceso',
    ];

    protected $casts = [
        'activo'        => 'boolean',
        'ultimo_acceso' => 'datetime',
    ];

    /* ── Relaciones ──────────────────────────────────────────────────────── */

    public function establecimiento(): BelongsTo
    {
        return $this->belongsTo(Establecimiento::class, 'id_establecimiento', 'id_establecimiento');
    }

    public function especialidad(): BelongsTo
    {
        return $this->belongsTo(CatEspecialidad::class, 'id_especialidad', 'id_especialidad');
    }

    public function historiasResponsable(): HasMany
    {
        return $this->hasMany(HistoriaClinica::class, 'id_medico_responsable', 'id_medico');
    }

    public function notasMedicas(): HasMany
    {
        return $this->hasMany(NotaMedica::class, 'id_medico', 'id_medico');
    }

    /* ── Accesores ───────────────────────────────────────────────────────── */

    public function getNombreCompletoAttribute(): string
    {
        return trim("{$this->nombre} {$this->primer_apellido} {$this->segundo_apellido}");
    }

    public function esAdmin(): bool
    {
        return $this->rol_sistema === 'admin';
    }

    /* ── Contract Authenticatable ────────────────────────────────────────── */

    public function getAuthIdentifierName()  { return 'id_medico'; }
    public function getAuthIdentifier()      { return $this->id_medico; }
    public function getAuthPassword()        { return ''; }          // auth vía Firebase
    public function getAuthPasswordName()    { return 'password'; }
    public function getRememberToken()       { return null; }
    public function setRememberToken($value) {}
    public function getRememberTokenName()   { return null; }
}
