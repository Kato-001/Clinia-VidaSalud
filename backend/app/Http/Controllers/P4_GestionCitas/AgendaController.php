<?php

namespace App\Http\Controllers\P4_GestionCitas;

use App\Http\Controllers\Controller;
use App\Models\BloqueoAgenda;
use App\Models\Cita;
use App\Models\Medico;
use App\Models\Rol;
use App\Services\AtencionService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

/** CU14 Consultar agenda médica (MEDICO.verAgenda, MEDICO.bloquearAgenda) */
class AgendaController extends Controller
{
    public function index(Request $request)
    {
        $usuario = $request->user();
        $fecha = Carbon::parse($request->input('fecha', today()->toDateString()));
        $medico = $usuario->tieneRol(Rol::MEDICO)
            ? $usuario->medico
            : Medico::where('estado', 'ACTIVO')->find($request->medico) ?? Medico::where('estado', 'ACTIVO')->orderBy('apellidos')->first();

        $citas = $medico
            ? Cita::with(['paciente', 'especialidad', 'estado', 'consulta'])->where('idmedico', $medico->idmedico)
                ->whereDate('fecha', $fecha)->orderBy('horainicio')->get()
            : collect();

        return view('P4_GestionCitas.CU14_ConsultarAgendaMedica.agenda', [
            'medico' => $medico,
            'fecha' => $fecha,
            'citas' => $citas,
            'horarios' => $medico?->horarios()->where('estado', 'ACTIVO')->where('diasemana', $fecha->isoWeekday())->get() ?? collect(),
            'bloqueos' => $medico ? BloqueoAgenda::where('idmedico', $medico->idmedico)->whereDate('fecha', $fecha)->get() : collect(),
            'medicos' => $usuario->esPersonal() ? Medico::where('estado', 'ACTIVO')->orderBy('apellidos')->get() : collect(),
        ]);
    }

    public function bloqueos(Request $request)
    {
        return view('P4_GestionCitas.CU14_ConsultarAgendaMedica.bloqueos', [
            'bloqueos' => $request->user()->medico->bloqueos()->whereDate('fecha', '>=', today())->orderBy('fecha')->orderBy('horainicio')->get(),
            'tipos' => BloqueoAgenda::TIPOS,
        ]);
    }

    public function bloquear(Request $request, AtencionService $atencion)
    {
        $datos = $request->validate([
            'fecha' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'horainicio' => ['nullable', 'required_unless:diacompleto,1', 'date_format:H:i'],
            'horafin' => ['nullable', 'required_unless:diacompleto,1', 'date_format:H:i', 'after:horainicio'],
            'tipo' => ['required', Rule::in(array_keys(BloqueoAgenda::TIPOS))],
            'motivo' => ['nullable', 'string', 'max:255'],
        ], ['horainicio.required_unless' => 'Indique la hora de inicio o marque «día completo».', 'horafin.required_unless' => 'Indique la hora de fin o marque «día completo».']);
        if ($request->boolean('diacompleto')) {
            $datos['horainicio'] = $datos['horafin'] = null;
        }

        $atencion->bloquear($request->user()->medico, $datos);

        return back()->with('ok', 'Agenda bloqueada. Esos horarios ya no se ofrecen para reservar.');
    }

    public function liberar(Request $request, BloqueoAgenda $bloqueo, AtencionService $atencion)
    {
        abort_unless($bloqueo->idmedico === $request->user()->medico?->idmedico, 404);
        $atencion->liberar($bloqueo);

        return back()->with('ok', 'Bloqueo liberado. Los horarios vuelven a estar disponibles.');
    }
}
