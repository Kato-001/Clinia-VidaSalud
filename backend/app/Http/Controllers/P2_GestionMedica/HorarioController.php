<?php

namespace App\Http\Controllers\P2_GestionMedica;

use App\Exceptions\ReglaNegocio;
use App\Http\Controllers\Controller;
use App\Models\Bitacora;
use App\Models\Horario;
use App\Models\Medico;
use App\Services\CitaService;
use Illuminate\Http\Request;

/** CU6 Gestionar horarios de atención */
class HorarioController extends Controller
{
    public function index(Medico $medico, CitaService $citas)
    {
        $medico->load(['horarios', 'especialidades']);

        return view('P2_GestionMedica.CU6_GestionarHorarios.index', [
            'medico' => $medico,
            'dias' => Horario::DIAS,
            'duracion' => $citas->duracion(),
        ]);
    }

    public function store(Request $request, Medico $medico)
    {
        $datos = $request->validate([
            'diasemana' => ['required', 'integer', 'between:1,7'],
            'horainicio' => ['required', 'date_format:H:i'],
            'horafin' => ['required', 'date_format:H:i', 'after:horainicio'],
        ]);

        $cruza = $medico->horarios()->where('diasemana', $datos['diasemana'])
            ->where('horainicio', '<', $datos['horafin'])->where('horafin', '>', $datos['horainicio'])->exists();
        if ($cruza) {
            throw new ReglaNegocio('Ese bloque se cruza con otro horario del mismo día.');
        }

        $horario = $medico->horarios()->create($datos + ['estado' => 'ACTIVO']);
        Bitacora::registrar('CREAR', 'GESTION_MEDICA', 'horario', $horario->idhorario, "Agregó horario {$horario->dia} {$datos['horainicio']}-{$datos['horafin']} a {$medico->nombre_completo}");

        return back()->with('ok', "Horario del {$horario->dia} agregado.");
    }

    public function estado(Medico $medico, Horario $horario)
    {
        abort_unless($horario->idmedico === $medico->idmedico, 404);
        $horario->update(['estado' => $horario->estado === 'ACTIVO' ? 'INACTIVO' : 'ACTIVO']);
        Bitacora::registrar('ACTUALIZAR', 'GESTION_MEDICA', 'horario', $horario->idhorario, "Cambió a {$horario->estado} el horario del {$horario->dia} de {$medico->nombre_completo}");

        return back()->with('ok', 'Horario actualizado.');
    }

    public function destroy(Medico $medico, Horario $horario)
    {
        abort_unless($horario->idmedico === $medico->idmedico, 404);
        $horario->delete();
        Bitacora::registrar('ELIMINAR', 'GESTION_MEDICA', 'horario', $horario->idhorario, "Quitó el horario del {$horario->dia} de {$medico->nombre_completo}");

        return back()->with('ok', 'Horario eliminado. Las citas ya reservadas se mantienen.');
    }
}
