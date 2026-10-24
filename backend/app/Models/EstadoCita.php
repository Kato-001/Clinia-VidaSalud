<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** ESTADOCITA (diagrama UML) */
class EstadoCita extends Model
{
    protected $table = 'estadocita';
    protected $primaryKey = 'codigo';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = ['codigo', 'nombre', 'descripcion', 'ocupahorario'];

    protected function casts(): array
    {
        return [
            'ocupahorario' => 'boolean',
        ];
    }

    public const PROGRAMADA = 'PROGRAMADA';
    public const CONFIRMADA = 'CONFIRMADA';
    public const REPROGRAMADA = 'REPROGRAMADA';
    public const EN_ATENCION = 'EN_ATENCION';
    public const ATENDIDA = 'ATENDIDA';
    public const CANCELADA = 'CANCELADA';
    public const NO_ASISTIO = 'NO_ASISTIO';

    /** Estados en los que la cita sigue pendiente de atención */
    public const PENDIENTES = [self::PROGRAMADA, self::CONFIRMADA, self::REPROGRAMADA];

    /** ESTADOCITA.ocupaHorario() */
    public function ocupaHorario(): bool
    {
        return (bool) $this->ocupahorario;
    }
}
