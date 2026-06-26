<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Catálogo: cat_estados_cita. */
class CatEstadoCita extends Model
{
    protected $table = 'cat_estados_cita';
    protected $primaryKey = 'id_estado_cita';
    public $timestamps = false;

    protected $fillable = ['descripcion'];
}
