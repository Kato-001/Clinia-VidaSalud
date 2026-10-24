<?php

namespace App\Http\Controllers\P4_GestionCitas;

use App\Http\Controllers\Controller;
use App\Models\Cita;
use App\Models\EstadoCita;
use App\Models\Medico;
use App\Models\Rol;
use App\Services\CitaService;
use Illuminate\Http\Request;

/** CU13 Registrar asistencia: el médico atiende (asistió) o se marca que no asistió */
class AsistenciaController extends Controller
{
    public function __construct(private CitaService $citas)
    {
    }

    public function index(Request $request)
    {
        $usuario = $request->user();
        $fecha = $request->input('fecha', today()->toDateString());
        $idmedico = $usuario->tieneRol(Rol::MEDICO) ? $usuario->medico?->idmedico : $request->medico;

        $citas = Cita::with(['paciente', 'medico', 'especialidad', 'estado'])
            ->whereDate('fecha', $fecha)
            ->when($idmedico, fn ($q) => $q->where('idmedico', $idmedico))
            ->whereIn('codigoestado', [...EstadoCita::PENDIENTES, EstadoCita::EN_ATENCION, EstadoCita::ATENDIDA, EstadoCita::NO_ASISTIO])
            ->orderBy('horainicio')->get();

        return view('P4_GestionCitas.CU13_RegistrarAsistencia.asistencia', [
            'citas' => $citas,
            'fecha' => $fecha,
            'medicos' => $usuario->esPersonal() ? Medico::where('estado', 'ACTIVO')->orderBy('apellidos')->get() : collect(),
            'esMedico' => $usuario->tieneRol(Rol::MEDICO),
        ]);
    }

    /** El paciente asistió: el médico inicia la atención (CU15) */
    public function atender(Request $request, Cita $cita)
    {
        abort_unless($this->citas->puedeAcceder($request->user(), $cita), 404);
        $consulta = $this->citas->atender($cita);

        return redirect()->route('consultas.show', $consulta)->with('ok', 'Asistencia registrada. Complete la consulta.');
    }

    public function noAsistio(Request $request, Cita $cita)
    {
        abort_unless($this->citas->puedeAcceder($request->user(), $cita), 404);
        $this->citas->marcarNoAsistio($cita);

        return back()->with('ok', "Se registró la inasistencia a la cita {$cita->codigo}.");
    }
}
