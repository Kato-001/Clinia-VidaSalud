<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** MEDICOESPECIALIDAD (diagrama UML) */
class MedicoEspecialidad extends Model
{
    protected $table = 'medicoespecialidad';
    protected $primaryKey = 'idmedico';
    public $timestamps = false;

    protected $fillable = ['idmedico', 'idespecialidad'];

    // Clave primaria compuesta (idmedico, idespecialidad): las asignaciones se hacen con
    // Medico::especialidades()->sync(); este modelo se usa para consultar.
    public $incrementing = false;

    /** MEDICOESPECIALIDAD N — 1 MEDICO (TIENE) */
    public function medico(): BelongsTo
    {
        return $this->belongsTo(Medico::class, 'idmedico', 'idmedico');
    }

    /** MEDICOESPECIALIDAD N — 1 ESPECIALIDAD (CLASIFICA) */
    public function especialidad(): BelongsTo
    {
        return $this->belongsTo(Especialidad::class, 'idespecialidad', 'idespecialidad');
    }
}
