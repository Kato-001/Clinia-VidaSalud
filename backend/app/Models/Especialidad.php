<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/** ESPECIALIDAD (diagrama UML) */
class Especialidad extends Model
{
    protected $table = 'especialidad';
    protected $primaryKey = 'idespecialidad';
    public $timestamps = false;

    protected $fillable = ['nombre', 'descripcion', 'estado'];

    /** ESPECIALIDAD N — N MEDICO, a través de MEDICOESPECIALIDAD */
    public function medicos(): BelongsToMany
    {
        return $this->belongsToMany(Medico::class, 'medicoespecialidad', 'idespecialidad', 'idmedico');
    }

    /** ESPECIALIDAD.listarMedicos(): médicos activos de la especialidad */
    public function listarMedicos()
    {
        return $this->medicos()->where('estado', 'ACTIVO')->orderBy('apellidos')->get();
    }
}
