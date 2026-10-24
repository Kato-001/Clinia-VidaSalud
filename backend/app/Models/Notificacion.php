<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** NOTIFICACION (diagrama UML) */
class Notificacion extends Model
{
    protected $table = 'notificacion';
    protected $primaryKey = 'idnotificacion';
    public $timestamps = false;

    protected $fillable = ['tipo', 'canal', 'mensaje', 'programadapara', 'enviadaen', 'leidaen', 'idcita', 'idestudio'];

    protected function casts(): array
    {
        return [
            'programadapara' => 'datetime',
            'enviadaen' => 'datetime',
            'leidaen' => 'datetime',
        ];
    }

    public const TIPOS = ['RESERVA', 'CONFIRMACION', 'REPROGRAMACION', 'CANCELACION', 'RECORDATORIO', 'RESULTADO', 'GENERAL'];
    public const CANALES = ['APP', 'EMAIL', 'SMS'];

    /** NOTIFICACION N — 0..1 CITA (GENERA) */
    public function cita(): BelongsTo
    {
        return $this->belongsTo(Cita::class, 'idcita', 'idcita');
    }

    /** NOTIFICACION N — 0..1 ESTUDIO (GENERA) */
    public function estudio(): BelongsTo
    {
        return $this->belongsTo(Estudio::class, 'idestudio', 'idestudio');
    }

    /** NOTIFICACION.programar() + enviar(): el canal APP se entrega al instante */
    public static function programar(string $tipo, string $mensaje, ?int $idcita = null, ?int $idestudio = null): self
    {
        return self::create([
            'tipo' => $tipo,
            'canal' => 'APP',
            'mensaje' => $mensaje,
            'enviadaen' => now(),
            'idcita' => $idcita,
            'idestudio' => $idestudio,
        ]);
    }

    /** NOTIFICACION.marcarLeida() */
    public function marcarLeida(): void
    {
        if (! $this->leidaen) {
            $this->update(['leidaen' => now()]);
        }
    }

    /** Notificaciones de un paciente: por su cita o por la cita de la consulta del estudio */
    public function scopeDePaciente(Builder $consulta, int $idpaciente): Builder
    {
        return $consulta->where(function ($q) use ($idpaciente) {
            $q->whereHas('cita', fn ($c) => $c->where('idpaciente', $idpaciente))
                ->orWhereHas('estudio.consulta.cita', fn ($c) => $c->where('idpaciente', $idpaciente));
        });
    }
}
