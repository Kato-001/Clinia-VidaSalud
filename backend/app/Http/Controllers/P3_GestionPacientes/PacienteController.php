<?php

namespace App\Http\Controllers\P3_GestionPacientes;

use App\Http\Controllers\Controller;
use App\Models\Paciente;
use App\Services\PacienteService;
use Illuminate\Http\Request;

/** CU7 Gestionar pacientes (recepción y administración) */
class PacienteController extends Controller
{
    use ReglasPaciente;

    public function __construct(private PacienteService $servicio)
    {
    }

    public function index(Request $request)
    {
        $pacientes = Paciente::query()
            ->when($request->q, fn ($q, $t) => $q->where(fn ($w) => $w->where('ci', 'ilike', "%{$t}%")->orWhere('nombres', 'ilike', "%{$t}%")
                ->orWhere('apellidos', 'ilike', "%{$t}%")->orWhere('codigo', 'ilike', "%{$t}%")))
            ->when($request->filled('estado'), fn ($q) => $q->where('estado', $request->estado))
            ->orderBy('apellidos')->orderBy('nombres')->paginate(15)->withQueryString();

        return view('P3_GestionPacientes.CU7_GestionarPacientes.index', compact('pacientes'));
    }

    /** Búsqueda para el formulario de citas (JSON) */
    public function buscar(Request $request)
    {
        $t = trim((string) $request->q);
        if (mb_strlen($t) < 2) {
            return response()->json([]);
        }

        return Paciente::where('estado', 'ACTIVO')
            ->where(fn ($w) => $w->where('ci', 'ilike', "%{$t}%")->orWhere('nombres', 'ilike', "%{$t}%")->orWhere('apellidos', 'ilike', "%{$t}%"))
            ->orderBy('apellidos')->limit(10)->get()
            ->map(fn ($p) => ['id' => $p->idpaciente, 'nombre' => $p->nombre_completo, 'codigo' => $p->codigo, 'ci' => $p->ci]);
    }

    public function show(Paciente $paciente)
    {
        $paciente->load(['historiaClinica', 'contactos', 'citas' => fn ($q) => $q->with(['medico', 'especialidad', 'estado'])->orderByDesc('fecha')->orderByDesc('horainicio')]);

        return view('P3_GestionPacientes.CU7_GestionarPacientes.show', compact('paciente'));
    }

    public function create()
    {
        return view('P3_GestionPacientes.CU7_GestionarPacientes.form', ['paciente' => new Paciente(['aceptanotificaciones' => true])]);
    }

    /** Registro en recepción: la cuenta del paciente usa su CI como usuario y contraseña inicial */
    public function store(Request $request)
    {
        $datos = $request->validate($this->reglasPaciente() + $this->reglasExtra());
        $datos['aceptanotificaciones'] = $request->boolean('aceptanotificaciones');
        $paciente = $this->servicio->registrar($datos + ['password' => $datos['ci']]);

        return redirect()->route('pacientes.show', $paciente)
            ->with('ok', "Paciente {$paciente->codigo} registrado. Puede ingresar a la web con su CI como usuario y contraseña inicial.");
    }

    public function edit(Paciente $paciente)
    {
        $paciente->load(['historiaClinica', 'contactos']);

        return view('P3_GestionPacientes.CU7_GestionarPacientes.form', compact('paciente'));
    }

    public function update(Request $request, Paciente $paciente)
    {
        $datos = $request->validate($this->reglasPaciente($paciente) + $this->reglasExtra());
        $datos['aceptanotificaciones'] = $request->boolean('aceptanotificaciones');
        $this->servicio->actualizar($paciente, $datos);

        return redirect()->route('pacientes.show', $paciente)->with('ok', 'Datos del paciente actualizados.');
    }

    public function estado(Paciente $paciente)
    {
        $this->servicio->cambiarEstado($paciente);

        return back()->with('ok', "El paciente quedó " . mb_strtolower($paciente->estado) . '. Su historial se conserva.');
    }

    private function reglasExtra(): array
    {
        return [
            'alergias' => ['nullable', 'string', 'max:255'],
            'contacto_nombre' => ['nullable', 'string', 'max:150', 'required_with:contacto_telefono'],
            'contacto_parentesco' => ['nullable', 'string', 'max:50'],
            'contacto_telefono' => ['nullable', 'regex:/^[0-9+\s\-]{7,20}$/', 'required_with:contacto_nombre'],
        ];
    }
}
