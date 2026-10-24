<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** MEDICO (diagrama UML) */
class Medico extends Model
{
    protected $table = 'medico';
    protected $primaryKey = 'idmedico';
    public $timestamps = false;

    protected $fillable = ['codigo', 'registroprofesional', 'nombres', 'apellidos', 'telefono', 'email', 'estado', 'idusuario'];

    /** MEDICO 0..1 — 1 USUARIO (PERTENECE) */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'idusuario', 'idusuario');
    }

    /** MEDICO N — N ESPECIALIDAD, a través de MEDICOESPECIALIDAD */
    public function especialidades(): BelongsToMany
    {
        return $this->belongsToMany(Especialidad::class, 'medicoespecialidad', 'idmedico', 'idespecialidad')->orderBy('nombre');
    }

    /** MEDICO 1 — N HORARIO */
    public function horarios(): HasMany
    {
        return $this->hasMany(Horario::class, 'idmedico', 'idmedico')->orderBy('diasemana')->orderBy('horainicio');
    }

    /** MEDICO 1 — N BLOQUEOAGENDA */
    public function bloqueos(): HasMany
    {
        return $this->hasMany(BloqueoAgenda::class, 'idmedico', 'idmedico');
    }

    /** MEDICO 1 — N CITA (ATIENDE) */
    public function citas(): HasMany
    {
        return $this->hasMany(Cita::class, 'idmedico', 'idmedico');
    }

    public function getNombreCompletoAttribute(): string
    {
        return "{$this->nombres} {$this->apellidos}";
    }
}
