<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Catálogo: cat_escolaridades. */
class CatEscolaridad extends Model
{
    protected $table = 'cat_escolaridades';
    protected $primaryKey = 'id_escolaridad';
    public $timestamps = false;

    protected $fillable = ['descripcion'];
}
