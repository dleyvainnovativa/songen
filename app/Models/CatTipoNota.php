<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Catálogo: cat_tipos_nota. */
class CatTipoNota extends Model
{
    protected $table = 'cat_tipos_nota';
    protected $primaryKey = 'id_tipo_nota';
    public $timestamps = false;

    protected $fillable = ['descripcion'];
}
