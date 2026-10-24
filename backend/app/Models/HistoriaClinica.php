<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** HISTORIACLINICA (diagrama UML) */
class HistoriaClinica extends Model
{
    protected $table = 'historiaclinica';
    protected $primaryKey = 'idhistoria';
    public $timestamps = false;

    protected $fillable = ['numero', 'antecedentespersonales', 'antecedentesfamiliares', 'alergias', 'enfermedadescronicas', 'medicacionhabitual', 'idpaciente'];

    public const ANTECEDENTES = [
        'alergias' => 'Alergias',
        'enfermedadescronicas' => 'Enfermedades crónicas',
        'medicacionhabitual' => 'Medicación habitual',
        'antecedentespersonales' => 'Antecedentes personales',
        'antecedentesfamiliares' => 'Antecedentes familiares',
    ];

    /** HISTORIACLINICA 0..1 — 1 PACIENTE (TIENE) */
    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class, 'idpaciente', 'idpaciente');
    }
}
