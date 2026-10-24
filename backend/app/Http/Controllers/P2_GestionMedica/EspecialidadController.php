<?php

namespace App\Http\Controllers\P2_GestionMedica;

use App\Http\Controllers\Controller;
use App\Models\Bitacora;
use App\Models\Especialidad;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** CU4 Gestionar especialidades */
class EspecialidadController extends Controller
{
    public function index(Request $request)
    {
        $especialidades = Especialidad::withCount('medicos')
            ->when($request->q, fn ($q, $t) => $q->where('nombre', 'ilike', "%{$t}%"))
            ->orderBy('nombre')->paginate(15)->withQueryString();

        return view('P2_GestionMedica.CU4_GestionarEspecialidades.index', compact('especialidades'));
    }

    public function create()
    {
        return view('P2_GestionMedica.CU4_GestionarEspecialidades.form', ['especialidad' => new Especialidad()]);
    }

    public function store(Request $request)
    {
        $especialidad = Especialidad::create($this->validar($request) + ['estado' => 'ACTIVO']);
        Bitacora::registrar('CREAR', 'GESTION_MEDICA', 'especialidad', $especialidad->idespecialidad, "Creó la especialidad {$especialidad->nombre}");

        return redirect()->route('especialidades.index')->with('ok', "Especialidad {$especialidad->nombre} registrada.");
    }

    public function edit(Especialidad $especialidad)
    {
        return view('P2_GestionMedica.CU4_GestionarEspecialidades.form', compact('especialidad'));
    }

    public function update(Request $request, Especialidad $especialidad)
    {
        $especialidad->update($this->validar($request, $especialidad));
        Bitacora::registrar('ACTUALIZAR', 'GESTION_MEDICA', 'especialidad', $especialidad->idespecialidad, "Actualizó la especialidad {$especialidad->nombre}");

        return redirect()->route('especialidades.index')->with('ok', 'Cambios guardados.');
    }

    public function estado(Especialidad $especialidad)
    {
        $especialidad->update(['estado' => $especialidad->estado === 'ACTIVO' ? 'INACTIVO' : 'ACTIVO']);
        Bitacora::registrar($especialidad->estado === 'ACTIVO' ? 'ACTIVAR' : 'DESACTIVAR', 'GESTION_MEDICA', 'especialidad', $especialidad->idespecialidad, "Cambió a {$especialidad->estado} la especialidad {$especialidad->nombre}");

        return back()->with('ok', "{$especialidad->nombre} quedó " . mb_strtolower($especialidad->estado) . '.');
    }

    private function validar(Request $request, ?Especialidad $especialidad = null): array
    {
        return $request->validate([
            'nombre' => ['required', 'string', 'min:3', 'max:100', 'regex:/^[\pL\s.\-]+$/u', Rule::unique('especialidad', 'nombre')->ignore($especialidad?->idespecialidad, 'idespecialidad')],
            'descripcion' => ['nullable', 'string', 'max:255'],
        ]);
    }
}
