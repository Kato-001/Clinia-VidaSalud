<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** CATALOGOESTUDIO (diagrama UML) */
class CatalogoEstudio extends Model
{
    protected $table = 'catalogoestudio';
    protected $primaryKey = 'idcatalogo';
    public $timestamps = false;

    protected $fillable = ['nombre', 'categoria', 'descripcion', 'estado'];

    /** CATALOGOESTUDIO 1 — N ESTUDIO */
    public function estudios(): HasMany
    {
        return $this->hasMany(Estudio::class, 'idcatalogo', 'idcatalogo');
    }

    /** CATALOGOESTUDIO.listar(): estudios activos */
    public static function listar()
    {
        return self::where('estado', 'ACTIVO')->orderBy('categoria')->orderBy('nombre')->get();
    }
}
