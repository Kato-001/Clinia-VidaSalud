<?php

namespace App\Http\Controllers\P4_GestionCitas;

use App\Http\Controllers\Controller;
use App\Models\Cita;
use App\Models\Especialidad;
use App\Models\EstadoCita;
use App\Models\Medico;
use App\Models\Paciente;
use App\Models\Rol;
use App\Services\CitaService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** CU10 Reservar ficha (programar cita) y consulta de citas */
class CitaController extends Controller
{
    public function __construct(private CitaService $citas)
    {
    }

    /** Listado según el rol: personal = todas, médico = las suyas, paciente = las suyas */
    public function index(Request $request)
    {
        $usuario = $request->user();
        $citas = Cita::with(['paciente', 'medico', 'especialidad', 'estado'])
            ->when($usuario->tieneRol(Rol::MEDICO), fn ($q) => $q->where('idmedico', $usuario->medico?->idmedico))
            ->when($usuario->tieneRol(Rol::PACIENTE), fn ($q) => $q->where('idpaciente', $usuario->paciente?->idpaciente))
            ->when($request->fecha, fn ($q, $f) => $q->whereDate('fecha', $f))
            ->when($request->medico, fn ($q, $m) => $q->where('idmedico', $m))
            ->when($request->estado, fn ($q, $e) => $q->where('codigoestado', $e))
            ->orderByDesc('fecha')->orderBy('horainicio')->paginate(15)->withQueryString();

        return view('P4_GestionCitas.CU10_ReservarFicha.index', [
            'citas' => $citas,
            'medicos' => $usuario->esPersonal() ? Medico::orderBy('apellidos')->get() : collect(),
            'estados' => EstadoCita::orderBy('nombre')->get(),
        ]);
    }

    public function create(Request $request)
    {
        $usuario = $request->user();
        $esPaciente = $usuario->tieneRol(Rol::PACIENTE);

        return view('P4_GestionCitas.CU10_ReservarFicha.create', [
            'esPaciente' => $esPaciente,
            'pacienteSeleccionado' => $esPaciente ? $usuario->paciente : Paciente::find($request->paciente),
            'especialidades' => Especialidad::where('estado', 'ACTIVO')->orderBy('nombre')->get(),
            'duracion' => $this->citas->duracion(),
        ]);
    }

    public function store(Request $request)
    {
        $usuario = $request->user();
        $datos = $request->validate([
            'idpaciente' => [$usuario->tieneRol(Rol::PACIENTE) ? 'nullable' : 'required', 'exists:paciente,idpaciente'],
            'idespecialidad' => ['required', 'exists:especialidad,idespecialidad'],
            'idmedico' => ['required', 'exists:medico,idmedico'],
            'fecha' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'horainicio' => ['required', 'date_format:H:i'],
            'modalidad' => ['required', Rule::in(array_keys(Cita::MODALIDADES))],
            'motivo' => ['nullable', 'string', 'max:255'],
        ], ['horainicio.required' => 'Seleccione un horario disponible.']);

        $paciente = $usuario->tieneRol(Rol::PACIENTE) ? $usuario->paciente : Paciente::findOrFail($datos['idpaciente']);
        $cita = $this->citas->reservar($paciente, $datos);

        return redirect()->route('citas.show', $cita)->with('ok', "Cita {$cita->codigo} reservada para el {$cita->fecha->format('d/m/Y')} a las {$cita->hora}.");
    }

    public function show(Request $request, Cita $cita)
    {
        abort_unless($this->citas->puedeAcceder($request->user(), $cita), 404);
        $cita->load(['paciente', 'medico', 'especialidad', 'estado', 'consulta', 'historial.estado']);

        return view('P4_GestionCitas.CU10_ReservarFicha.show', compact('cita'));
    }

    /** PACIENTE.confirmarCita() */
    public function confirmar(Request $request, Cita $cita)
    {
        abort_unless($this->citas->puedeAcceder($request->user(), $cita), 404);
        $this->citas->confirmar($cita);

        return back()->with('ok', 'Asistencia confirmada. ¡Lo esperamos!');
    }
}
