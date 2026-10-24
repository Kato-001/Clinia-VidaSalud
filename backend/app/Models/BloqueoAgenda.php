<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** BLOQUEOAGENDA (diagrama UML) */
class BloqueoAgenda extends Model
{
    protected $table = 'bloqueoagenda';
    protected $primaryKey = 'idbloqueo';
    public $timestamps = false;

    protected $fillable = ['fecha', 'horainicio', 'horafin', 'tipo', 'motivo', 'idmedico'];

    protected function casts(): array
    {
        return [
            'fecha' => 'date:Y-m-d',
        ];
    }

    public const TIPOS = ['VACACIONES' => 'Vacaciones', 'PERMISO' => 'Permiso', 'CAPACITACION' => 'Capacitación', 'EMERGENCIA' => 'Emergencia', 'OTRO' => 'Otro'];

    /** BLOQUEOAGENDA N — 1 MEDICO (TIENE) */
    public function medico(): BelongsTo
    {
        return $this->belongsTo(Medico::class, 'idmedico', 'idmedico');
    }

    /** Sin horas = bloquea el día completo */
    public function esDiaCompleto(): bool
    {
        return $this->horainicio === null;
    }
}
