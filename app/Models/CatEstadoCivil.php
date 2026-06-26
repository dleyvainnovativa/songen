<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Catálogo: cat_estados_civiles. */
class CatEstadoCivil extends Model
{
    protected $table = 'cat_estados_civiles';
    protected $primaryKey = 'id_estado_civil';
    public $timestamps = false;

    protected $fillable = ['descripcion'];
}
