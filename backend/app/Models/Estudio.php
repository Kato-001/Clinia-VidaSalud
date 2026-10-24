<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** ESTUDIO (diagrama UML) */
class Estudio extends Model
{
    protected $table = 'estudio';
    protected $primaryKey = 'idestudio';
    public $timestamps = false;

    protected $fillable = ['indicaciones', 'estado', 'fechasolicitud', 'fecharesultado', 'resultado', 'archivoresultado', 'idconsulta', 'idcatalogo'];

    protected function casts(): array
    {
        return [
            'fechasolicitud' => 'datetime',
            'fecharesultado' => 'datetime',
        ];
    }

    public const ESTADOS = ['SOLICITADO', 'COMPLETADO', 'ANULADO'];

    /** ESTUDIO N — 1 CONSULTA (SOLICITA) */
    public function consulta(): BelongsTo
    {
        return $this->belongsTo(Consulta::class, 'idconsulta', 'idconsulta');
    }

    /** ESTUDIO N — 1 CATALOGOESTUDIO (CLASIFICA) */
    public function catalogo(): BelongsTo
    {
        return $this->belongsTo(CatalogoEstudio::class, 'idcatalogo', 'idcatalogo');
    }

    /** ESTUDIO 1 — N NOTIFICACION (GENERA) */
    public function notificaciones(): HasMany
    {
        return $this->hasMany(Notificacion::class, 'idestudio', 'idestudio');
    }
}
