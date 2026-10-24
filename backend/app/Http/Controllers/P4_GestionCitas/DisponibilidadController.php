<?php

namespace App\Http\Controllers\P4_GestionCitas;

use App\Http\Controllers\Controller;
use App\Models\Especialidad;
use App\Models\Horario;
use App\Models\Medico;
use App\Services\CitaService;
use Illuminate\Http\Request;

/** CU9 Consultar disponibilidad: portal público y servicios JSON del formulario de citas */
class DisponibilidadController extends Controller
{
    public function inicio(CitaService $citas)
    {
        return view('P4_GestionCitas.CU9_ConsultarDisponibilidad.inicio', [
            'especialidades' => Especialidad::where('estado', 'ACTIVO')
                ->withCount(['medicos' => fn ($q) => $q->where('estado', 'ACTIVO')])->orderBy('nombre')->get(),
            'medicos' => Medico::where('estado', 'ACTIVO')
                ->with(['especialidades', 'horarios' => fn ($q) => $q->where('estado', 'ACTIVO')])->orderBy('apellidos')->get(),
            'duracion' => $citas->duracion(),
        ]);
    }

    public function horarios(CitaService $citas)
    {
        return view('P4_GestionCitas.CU9_ConsultarDisponibilidad.horarios', [
            'especialidades' => Especialidad::where('estado', 'ACTIVO')->orderBy('nombre')->get(),
            'duracion' => $citas->duracion(),
        ]);
    }

    /** ESPECIALIDAD.listarMedicos() en JSON: [{id, nombre, dias}] */
    public function medicos(Especialidad $especialidad)
    {
        return $especialidad->medicos()->where('medico.estado', 'ACTIVO')
            ->with(['horarios' => fn ($q) => $q->where('estado', 'ACTIVO')])->orderBy('apellidos')->get()
            ->map(fn ($m) => [
                'id' => $m->idmedico,
                'nombre' => 'Dr(a). ' . $m->nombre_completo,
                'dias' => $m->horarios->pluck('diasemana')->unique()->sort()->map(fn ($d) => Horario::DIAS[$d])->values(),
            ]);
    }

    /** HORARIO.consultarDisponibilidad() en JSON: {bloques: [{inicio, fin, disponible}], mensaje} */
    public function bloques(Request $request, Medico $medico, CitaService $citas)
    {
        $request->validate(['fecha' => ['required', 'date_format:Y-m-d']]);

        return $citas->disponibilidad($medico, $request->fecha);
    }
}
