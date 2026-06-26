<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Establecimiento (clínica). Raíz del multi-tenant ligero por id_establecimiento. */
class Establecimiento extends Model
{
    protected $table = 'establecimientos';
    protected $primaryKey = 'id_establecimiento';
    public $timestamps = false;

    protected $fillable = [
        'nombre', 'razon_social', 'rfc', 'licencia_sanitaria', 'domicilio',
        'colonia', 'municipio', 'estado', 'cp', 'telefono', 'email',
        'nivel_atencion', 'fecha_registro',
    ];

    protected $casts = ['fecha_registro' => 'date'];

    public function pacientes(): HasMany
    {
        return $this->hasMany(Paciente::class, 'id_establecimiento', 'id_establecimiento');
    }

    public function personalMedico(): HasMany
    {
        return $this->hasMany(PersonalMedico::class, 'id_establecimiento', 'id_establecimiento');
    }
}
