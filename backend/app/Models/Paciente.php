<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/** PACIENTE (diagrama UML) */
class Paciente extends Model
{
    protected $table = 'paciente';
    protected $primaryKey = 'idpaciente';
    public $timestamps = false;

    protected $fillable = ['codigo', 'ci', 'nombres', 'apellidos', 'fechanacimiento', 'sexo', 'telefono', 'email', 'direccion', 'tiposangre', 'aceptanotificaciones', 'estado', 'idusuario'];

    protected function casts(): array
    {
        return [
            'fechanacimiento' => 'date:Y-m-d',
            'aceptanotificaciones' => 'boolean',
        ];
    }

    public const TIPOS_SANGRE = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
    public const SEXOS = ['F' => 'Femenino', 'M' => 'Masculino', 'O' => 'Otro'];

    /** PACIENTE 0..1 — 1 USUARIO (PERTENECE) */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'idusuario', 'idusuario');
    }

    /** PACIENTE 1 — N CONTACTOEMERGENCIA */
    public function contactos(): HasMany
    {
        return $this->hasMany(ContactoEmergencia::class, 'idpaciente', 'idpaciente')->orderBy('idcontacto');
    }

    /** PACIENTE 1 — 0..1 HISTORIACLINICA */
    public function historiaClinica(): HasOne
    {
        return $this->hasOne(HistoriaClinica::class, 'idpaciente', 'idpaciente');
    }

    /** PACIENTE 1 — N CITA (RESERVA) */
    public function citas(): HasMany
    {
        return $this->hasMany(Cita::class, 'idpaciente', 'idpaciente');
    }

    public function getNombreCompletoAttribute(): string
    {
        return "{$this->nombres} {$this->apellidos}";
    }

    public function getEdadAttribute(): int
    {
        return $this->fechanacimiento ? (int) $this->fechanacimiento->age : 0;
    }
}
