<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** CONSULTA (diagrama UML) */
class Consulta extends Model
{
    protected $table = 'consulta';
    protected $primaryKey = 'idconsulta';
    public $timestamps = false;

    protected $fillable = ['fechahora', 'motivo', 'peso', 'talla', 'presionarterial', 'temperatura', 'frecuenciacardiaca', 'examenfisico', 'diagnostico', 'observaciones', 'idcita'];

    protected function casts(): array
    {
        return [
            'fechahora' => 'datetime',
            'peso' => 'float',
            'talla' => 'float',
            'temperatura' => 'float',
            'frecuenciacardiaca' => 'integer',
        ];
    }

    /** CONSULTA 0..1 — 1 CITA (GENERA) */
    public function cita(): BelongsTo
    {
        return $this->belongsTo(Cita::class, 'idcita', 'idcita');
    }

    /** CONSULTA 1 — N TRATAMIENTO (INDICA) */
    public function tratamientos(): HasMany
    {
        return $this->hasMany(Tratamiento::class, 'idconsulta', 'idconsulta')->orderBy('idtratamiento');
    }

    /** CONSULTA 1 — N ESTUDIO (SOLICITA) */
    public function estudios(): HasMany
    {
        return $this->hasMany(Estudio::class, 'idconsulta', 'idconsulta')->orderBy('idestudio');
    }
}
