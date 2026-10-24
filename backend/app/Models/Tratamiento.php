<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** TRATAMIENTO (diagrama UML) */
class Tratamiento extends Model
{
    protected $table = 'tratamiento';
    protected $primaryKey = 'idtratamiento';
    public $timestamps = false;

    protected $fillable = ['dosis', 'frecuencia', 'duracion', 'indicaciones', 'idconsulta', 'idmedicamento'];

    /** TRATAMIENTO N — 1 CONSULTA (INDICA) */
    public function consulta(): BelongsTo
    {
        return $this->belongsTo(Consulta::class, 'idconsulta', 'idconsulta');
    }

    /** TRATAMIENTO N — 1 MEDICAMENTO (SE USA EN) */
    public function medicamento(): BelongsTo
    {
        return $this->belongsTo(Medicamento::class, 'idmedicamento', 'idmedicamento');
    }
}
