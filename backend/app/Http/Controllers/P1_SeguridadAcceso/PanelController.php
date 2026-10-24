<?php

namespace App\Http\Controllers\P1_SeguridadAcceso;

use App\Http\Controllers\Controller;
use App\Models\Cita;
use App\Models\EstadoCita;
use App\Models\Medico;
use App\Models\Notificacion;
use App\Models\Paciente;
use App\Models\Rol;
use Illuminate\Http\Request;

/** Página de inicio según el rol */
class PanelController extends Controller
{
    public function __invoke(Request $request)
    {
        $usuario = $request->user();

        if ($usuario->tieneRol(Rol::MEDICO)) {
            return redirect()->route('agenda.index');
        }

        if ($usuario->tieneRol(Rol::PACIENTE)) {
            $paciente = $usuario->paciente;
            return view('compartido.dashboard-paciente', [
                'paciente' => $paciente,
                'proximas' => Cita::with(['medico', 'especialidad', 'estado'])
                    ->where('idpaciente', $paciente?->idpaciente)
                    ->whereDate('fecha', '>=', today())
                    ->whereIn('codigoestado', [...EstadoCita::PENDIENTES, EstadoCita::EN_ATENCION])
                    ->orderBy('fecha')->orderBy('horainicio')->limit(5)->get(),
                'noLeidas' => $paciente ? Notificacion::dePaciente($paciente->idpaciente)->whereNull('leidaen')->count() : 0,
            ]);
        }

        return view('compartido.dashboard', [
            'totales' => [
                'citasHoy' => Cita::whereDate('fecha', today())->count(),
                'proximas' => Cita::whereDate('fecha', '>', today())->whereIn('codigoestado', EstadoCita::PENDIENTES)->count(),
                'pacientes' => Paciente::where('estado', 'ACTIVO')->count(),
                'medicos' => Medico::where('estado', 'ACTIVO')->count(),
            ],
            'citasHoy' => Cita::with(['paciente', 'medico', 'especialidad', 'estado'])
                ->whereDate('fecha', today())->orderBy('horainicio')->get(),
        ]);
    }
}
