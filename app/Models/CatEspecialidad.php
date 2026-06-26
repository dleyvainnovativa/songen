<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Catálogo: cat_especialidades. */
class CatEspecialidad extends Model
{
    protected $table = 'cat_especialidades';
    protected $primaryKey = 'id_especialidad';
    public $timestamps = false;

    protected $fillable = ['nombre', 'cedula_requerida'];
    protected $casts = ['cedula_requerida' => 'boolean'];
}
