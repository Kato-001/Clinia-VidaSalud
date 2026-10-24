<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;

/** USUARIO (diagrama UML): cuenta de acceso con su ROL */
class Usuario extends Authenticatable
{
    protected $table = 'usuario';
    protected $primaryKey = 'idusuario';
    public $timestamps = false;

    protected $fillable = ['nombre', 'username', 'email', 'password', 'estado', 'ultimoacceso', 'idrol'];

    protected $hidden = ['password'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'ultimoacceso' => 'datetime',
        ];
    }

    /** La tabla USUARIO no tiene columna para "recordarme" */
    public function getRememberTokenName()
    {
        return '';
    }

    /** USUARIO N — 1 ROL (TIENE) */
    public function rol(): BelongsTo
    {
        return $this->belongsTo(Rol::class, 'idrol', 'idrol');
    }

    /** USUARIO 1 — 0..1 MEDICO (PERTENECE) */
    public function medico(): HasOne
    {
        return $this->hasOne(Medico::class, 'idusuario', 'idusuario');
    }

    /** USUARIO 1 — 0..1 PACIENTE (PERTENECE) */
    public function paciente(): HasOne
    {
        return $this->hasOne(Paciente::class, 'idusuario', 'idusuario');
    }

    /** USUARIO 1 — N BITACORA (REGISTRA) */
    public function bitacoras(): HasMany
    {
        return $this->hasMany(Bitacora::class, 'idusuario', 'idusuario');
    }

    /** ¿El usuario tiene alguno de estos roles? (ADMINISTRADOR, RECEPCIONISTA, MEDICO, PACIENTE) */
    public function tieneRol(string ...$roles): bool
    {
        return in_array($this->rol?->nombre, array_map('strtoupper', $roles), true);
    }

    /** Personal de la clínica que gestiona citas y pacientes */
    public function esPersonal(): bool
    {
        return $this->tieneRol(Rol::ADMINISTRADOR, Rol::RECEPCIONISTA);
    }

    /** Nombre del rol para mostrar */
    public function getNombreRolAttribute(): string
    {
        return match ($this->rol?->nombre) {
            Rol::ADMINISTRADOR => 'Administrador',
            Rol::RECEPCIONISTA => 'Recepcionista',
            Rol::MEDICO => 'Médico',
            Rol::PACIENTE => 'Paciente',
            default => (string) $this->rol?->nombre,
        };
    }
}
