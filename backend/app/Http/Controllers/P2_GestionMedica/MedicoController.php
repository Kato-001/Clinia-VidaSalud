<?php

namespace App\Http\Controllers\P2_GestionMedica;

use App\Http\Controllers\Controller;
use App\Models\Bitacora;
use App\Models\Especialidad;
use App\Models\Medico;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/** CU5 Gestionar médicos (MEDICO + su USUARIO + MEDICOESPECIALIDAD) */
class MedicoController extends Controller
{
    public function index(Request $request)
    {
        $medicos = Medico::with('especialidades')
            ->when($request->q, fn ($q, $t) => $q->where(fn ($w) => $w->where('nombres', 'ilike', "%{$t}%")->orWhere('apellidos', 'ilike', "%{$t}%")->orWhere('codigo', 'ilike', "%{$t}%")))
            ->when($request->especialidad, fn ($q, $e) => $q->whereHas('especialidades', fn ($w) => $w->where('especialidad.idespecialidad', $e)))
            ->orderBy('apellidos')->orderBy('nombres')->paginate(15)->withQueryString();

        return view('P2_GestionMedica.CU5_GestionarMedicos.index', ['medicos' => $medicos, 'especialidades' => Especialidad::orderBy('nombre')->get()]);
    }

    public function show(Medico $medico)
    {
        $medico->load(['especialidades', 'horarios', 'usuario']);
        $duracion = app(\App\Services\CitaService::class)->duracion();

        return view('P2_GestionMedica.CU5_GestionarMedicos.show', compact('medico', 'duracion'));
    }

    public function create()
    {
        $siguiente = (int) DB::table('medico')->max('idmedico') + 1;
        $medico = new Medico(['codigo' => 'MED-' . str_pad((string) $siguiente, 4, '0', STR_PAD_LEFT)]);

        return view('P2_GestionMedica.CU5_GestionarMedicos.form', ['medico' => $medico, 'especialidades' => Especialidad::where('estado', 'ACTIVO')->orderBy('nombre')->get()]);
    }

    /** Alta del médico con su cuenta de acceso (rol MEDICO) */
    public function store(Request $request)
    {
        $datos = $this->validar($request);
        $cuenta = $request->validate([
            'username' => ['required', 'string', 'min:3', 'max:50', 'regex:/^[A-Za-z0-9._-]+$/', 'unique:usuario,username'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ]);
        $request->validate(['email' => ['required', 'unique:usuario,email']]);

        $medico = DB::transaction(function () use ($datos, $cuenta) {
            $usuario = Usuario::create([
                'nombre' => "{$datos['nombres']} {$datos['apellidos']}",
                'username' => mb_strtolower($cuenta['username']),
                'email' => $datos['email'],
                'password' => $cuenta['password'],
                'estado' => 'ACTIVO',
                'idrol' => Rol::where('nombre', Rol::MEDICO)->value('idrol'),
            ]);
            $medico = Medico::create(collect($datos)->except('especialidades')->all() + ['idusuario' => $usuario->idusuario, 'estado' => 'ACTIVO']);
            $medico->especialidades()->sync($datos['especialidades']);
            Bitacora::registrar('CREAR', 'GESTION_MEDICA', 'medico', $medico->idmedico, "Registró al médico {$medico->nombre_completo} (usuario {$usuario->username})");

            return $medico;
        });

        return redirect()->route('medicos.show', $medico)->with('ok', "Médico registrado. Puede ingresar con el usuario {$cuenta['username']}.");
    }

    public function edit(Medico $medico)
    {
        $medico->load('especialidades');

        return view('P2_GestionMedica.CU5_GestionarMedicos.form', ['medico' => $medico, 'especialidades' => Especialidad::where('estado', 'ACTIVO')->orderBy('nombre')->get()]);
    }

    public function update(Request $request, Medico $medico)
    {
        $datos = $this->validar($request, $medico);
        DB::transaction(function () use ($medico, $datos) {
            $medico->update(collect($datos)->except('especialidades')->all());
            $medico->especialidades()->sync($datos['especialidades']);
            $medico->usuario->update(['nombre' => $medico->nombre_completo]);
            Bitacora::registrar('ACTUALIZAR', 'GESTION_MEDICA', 'medico', $medico->idmedico, "Actualizó al médico {$medico->nombre_completo}");
        });

        return redirect()->route('medicos.show', $medico)->with('ok', 'Cambios guardados.');
    }

    /** Un médico inactivo no recibe nuevas citas; su cuenta también se desactiva */
    public function estado(Medico $medico)
    {
        DB::transaction(function () use ($medico) {
            $estado = $medico->estado === 'ACTIVO' ? 'INACTIVO' : 'ACTIVO';
            $medico->update(['estado' => $estado]);
            $medico->usuario->update(['estado' => $estado]);
            Bitacora::registrar($estado === 'ACTIVO' ? 'ACTIVAR' : 'DESACTIVAR', 'GESTION_MEDICA', 'medico', $medico->idmedico, "Cambió a {$estado} al médico {$medico->nombre_completo}");
        });

        return back()->with('ok', "{$medico->nombre_completo} quedó " . mb_strtolower($medico->estado) . '.');
    }

    private function validar(Request $request, ?Medico $medico = null): array
    {
        return $request->validate([
            'codigo' => ['required', 'string', 'max:20', Rule::unique('medico', 'codigo')->ignore($medico?->idmedico, 'idmedico')],
            'nombres' => ['required', 'string', 'min:2', 'max:100', 'regex:/^[\pL\s]+$/u'],
            'apellidos' => ['required', 'string', 'min:2', 'max:100', 'regex:/^[\pL\s]+$/u'],
            'registroprofesional' => ['required', 'string', 'max:50', Rule::unique('medico', 'registroprofesional')->ignore($medico?->idmedico, 'idmedico')],
            'telefono' => ['required', 'regex:/^[0-9+\s\-]{7,20}$/'],
            'email' => ['required', 'email', 'max:150'],
            'especialidades' => ['required', 'array', 'min:1'],
            'especialidades.*' => ['integer', 'exists:especialidad,idespecialidad'],
        ]);
    }
}
