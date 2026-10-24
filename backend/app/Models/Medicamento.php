<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** MEDICAMENTO (diagrama UML) */
class Medicamento extends Model
{
    protected $table = 'medicamento';
    protected $primaryKey = 'idmedicamento';
    public $timestamps = false;

    protected $fillable = ['nombre', 'presentacion', 'estado'];

    /** MEDICAMENTO 1 — N TRATAMIENTO */
    public function tratamientos(): HasMany
    {
        return $this->hasMany(Tratamiento::class, 'idmedicamento', 'idmedicamento');
    }

    /** MEDICAMENTO.buscar(): medicamentos activos por nombre */
    public static function buscar(string $texto = '')
    {
        return self::where('estado', 'ACTIVO')
            ->when($texto !== '', fn ($q) => $q->where('nombre', 'ilike', "%{$texto}%"))
            ->orderBy('nombre')
            ->get();
    }
}
