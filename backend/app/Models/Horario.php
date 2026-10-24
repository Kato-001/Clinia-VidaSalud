<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** HORARIO (diagrama UML) */
class Horario extends Model
{
    protected $table = 'horario';
    protected $primaryKey = 'idhorario';
    public $timestamps = false;

    protected $fillable = ['diasemana', 'horainicio', 'horafin', 'estado', 'idmedico'];

    protected function casts(): array
    {
        return [
            'diasemana' => 'integer',
        ];
    }

    public const DIAS = [1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado', 7 => 'Domingo'];

    /** HORARIO N — 1 MEDICO (TIENE) */
    public function medico(): BelongsTo
    {
        return $this->belongsTo(Medico::class, 'idmedico', 'idmedico');
    }

    public function getDiaAttribute(): string
    {
        return self::DIAS[$this->diasemana] ?? '';
    }
}
