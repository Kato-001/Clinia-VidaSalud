<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** HISTORIALCITA (diagrama UML) */
class HistorialCita extends Model
{
    protected $table = 'historialcita';
    protected $primaryKey = 'idhistorial';
    public $timestamps = false;

    protected $fillable = ['accion', 'fechaanterior', 'horaanterior', 'fechanueva', 'horanueva', 'motivo', 'fecha', 'idcita', 'codigoestado'];

    protected function casts(): array
    {
        return [
            'fechaanterior' => 'date:Y-m-d',
            'fechanueva' => 'date:Y-m-d',
            'fecha' => 'date:Y-m-d',
        ];
    }

    /** HISTORIALCITA N — 1 CITA (TIENE) */
    public function cita(): BelongsTo
    {
        return $this->belongsTo(Cita::class, 'idcita', 'idcita');
    }

    /** HISTORIALCITA N — 1 ESTADOCITA (REGISTRA) */
    public function estado(): BelongsTo
    {
        return $this->belongsTo(EstadoCita::class, 'codigoestado', 'codigo');
    }

    /** HISTORIALCITA.registrarCambio(): guarda el cambio con el estado actual de la cita */
    public static function registrarCambio(Cita $cita, string $accion, array $datos = []): self
    {
        return self::create([
            'accion' => $accion,
            'fechaanterior' => $datos['fechaanterior'] ?? null,
            'horaanterior' => $datos['horaanterior'] ?? null,
            'fechanueva' => $datos['fechanueva'] ?? null,
            'horanueva' => $datos['horanueva'] ?? null,
            'motivo' => $datos['motivo'] ?? null,
            'fecha' => now()->toDateString(),
            'idcita' => $cita->idcita,
            'codigoestado' => $cita->codigoestado,
        ]);
    }
}
