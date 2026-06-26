<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Medicamento extends Model
{
    protected $table = 'medicamentos';
    protected $primaryKey = 'id_medicamento';
    public $timestamps = false;

    protected $fillable = [
        'nombre_generico', 'nombre_comercial', 'forma_farmaceutica',
        'concentracion', 'via_administracion', 'activo',
    ];

    protected $casts = ['activo' => 'boolean'];
}
