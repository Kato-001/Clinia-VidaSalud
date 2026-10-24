<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** CONTACTOEMERGENCIA (diagrama UML) */
class ContactoEmergencia extends Model
{
    protected $table = 'contactoemergencia';
    protected $primaryKey = 'idcontacto';
    public $timestamps = false;

    protected $fillable = ['nombre', 'parentesco', 'telefono', 'idpaciente'];

    /** CONTACTOEMERGENCIA N — 1 PACIENTE (TIENE) */
    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class, 'idpaciente', 'idpaciente');
    }
}
