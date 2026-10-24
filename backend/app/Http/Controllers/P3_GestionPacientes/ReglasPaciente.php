<?php

namespace App\Http\Controllers\P3_GestionPacientes;

use App\Models\Paciente;
use Illuminate\Validation\Rule;

/** Reglas de validación de PACIENTE compartidas por registro, recepción y perfil */
trait ReglasPaciente
{
    protected function reglasPaciente(?Paciente $paciente = null): array
    {
        return [
            'ci' => ['required', 'regex:/^[0-9]{5,12}(-?[A-Za-z0-9]{1,3})?$/', Rule::unique('paciente', 'ci')->ignore($paciente?->idpaciente, 'idpaciente')],
            'nombres' => ['required', 'string', 'min:2', 'max:100', 'regex:/^[\pL\s]+$/u'],
            'apellidos' => ['required', 'string', 'min:2', 'max:100', 'regex:/^[\pL\s]+$/u'],
            'fechanacimiento' => ['required', 'date', 'before_or_equal:today', 'after:1900-01-01'],
            'sexo' => ['required', Rule::in(array_keys(Paciente::SEXOS))],
            'telefono' => ['required', 'regex:/^[0-9+\s\-]{7,20}$/'],
            'email' => ['required', 'email', 'max:150', Rule::unique('usuario', 'email')->ignore($paciente?->idusuario, 'idusuario')],
            'direccion' => ['nullable', 'string', 'max:255'],
            'tiposangre' => ['nullable', Rule::in(Paciente::TIPOS_SANGRE)],
        ];
    }
}
