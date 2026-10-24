<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** ROL (diagrama UML) */
class Rol extends Model
{
    protected $table = 'rol';
    protected $primaryKey = 'idrol';
    public $timestamps = false;

    protected $fillable = ['nombre', 'descripcion'];

    public const ADMINISTRADOR = 'ADMINISTRADOR';
    public const RECEPCIONISTA = 'RECEPCIONISTA';
    public const MEDICO = 'MEDICO';
    public const PACIENTE = 'PACIENTE';

    /** ROL 1 — N USUARIO (TIENE) */
    public function usuarios(): HasMany
    {
        return $this->hasMany(Usuario::class, 'idrol', 'idrol');
    }

    /** ROL.asignarPermisos() / quitarPermisos(): los permisos se definen por el nombre del rol en las rutas */
    public function tienePermiso(string ...$roles): bool
    {
        return in_array($this->nombre, $roles, true);
    }
}
