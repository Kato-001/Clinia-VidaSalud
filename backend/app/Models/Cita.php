<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/** CITA (diagrama UML) */
class Cita extends Model
{
    protected $table = 'cita';
    protected $primaryKey = 'idcita';
    public $timestamps = false;

    protected $fillable = ['codigo', 'fecha', 'horainicio', 'horafin', 'modalidad', 'motivo', 'idpaciente', 'idmedico', 'idespecialidad', 'codigoestado'];

    protected function casts(): array
    {
        return [
            'fecha' => 'date:Y-m-d',
        ];
    }

    public const MODALIDADES = ['PRESENCIAL' => 'Presencial', 'VIRTUAL' => 'Virtual'];

    /** CITA N — 1 PACIENTE (RESERVA) */
    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class, 'idpaciente', 'idpaciente');
    }

    /** CITA N — 1 MEDICO (ATIENDE) */
    public function medico(): BelongsTo
    {
        return $this->belongsTo(Medico::class, 'idmedico', 'idmedico');
    }

    /** CITA N — 1 ESPECIALIDAD (TIENE) */
    public function especialidad(): BelongsTo
    {
        return $this->belongsTo(Especialidad::class, 'idespecialidad', 'idespecialidad');
    }

    /** CITA N — 1 ESTADOCITA (CLASIFICA) */
    public function estado(): BelongsTo
    {
        return $this->belongsTo(EstadoCita::class, 'codigoestado', 'codigo');
    }

    /** CITA 1 — N HISTORIALCITA */
    public function historial(): HasMany
    {
        return $this->hasMany(HistorialCita::class, 'idcita', 'idcita')->orderBy('idhistorial');
    }

    /** CITA 1 — 0..1 CONSULTA (GENERA) */
    public function consulta(): HasOne
    {
        return $this->hasOne(Consulta::class, 'idcita', 'idcita');
    }

    /** CITA 1 — N NOTIFICACION (GENERA) */
    public function notificaciones(): HasMany
    {
        return $this->hasMany(Notificacion::class, 'idcita', 'idcita');
    }

    /** Sigue pendiente de atención (programada, confirmada o reprogramada) */
    public function estaPendiente(): bool
    {
        return in_array($this->codigoestado, EstadoCita::PENDIENTES, true);
    }

    /** Fecha 'Y-m-d' */
    public function getDiaAttribute(): string
    {
        return $this->fecha->format('Y-m-d');
    }

    /** Rango 'H:i – H:i' */
    public function getHorarioAttribute(): string
    {
        return substr((string) $this->horainicio, 0, 5) . ' – ' . substr((string) $this->horafin, 0, 5);
    }

    /** Hora de inicio 'H:i' */
    public function getHoraAttribute(): string
    {
        return substr((string) $this->horainicio, 0, 5);
    }
}
