<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Catálogo: cat_tipos_sangre. */
class CatTipoSangre extends Model
{
    protected $table = 'cat_tipos_sangre';
    protected $primaryKey = 'id_tipo_sangre';
    public $timestamps = false;

    protected $fillable = ['descripcion'];
}
