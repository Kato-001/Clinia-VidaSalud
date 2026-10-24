<?php

namespace App\Http\Controllers\P4_GestionCitas;

use App\Http\Controllers\Controller;
use App\Models\Cita;
use App\Services\CitaService;
use Illuminate\Http\Request;

/** CU11 Reprogramar cita */
class ReprogramacionController extends Controller
{
    public function __construct(private CitaService $citas)
    {
    }

    public function formulario(Request $request, Cita $cita)
    {
        abort_unless($this->citas->puedeAcceder($request->user(), $cita), 404);
        $cita->load(['paciente', 'medico', 'especialidad']);

        return view('P4_GestionCitas.CU11_ReprogramarCita.reprogramar', ['cita' => $cita, 'duracion' => $this->citas->duracion()]);
    }

    public function guardar(Request $request, Cita $cita)
    {
        abort_unless($this->citas->puedeAcceder($request->user(), $cita), 404);
        $datos = $request->validate([
            'fecha' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'horainicio' => ['required', 'date_format:H:i'],
            'motivo' => ['nullable', 'string', 'max:255'],
        ], ['horainicio.required' => 'Seleccione el nuevo horario.']);

        $cita = $this->citas->reprogramar($cita, $datos['fecha'], $datos['horainicio'], $datos['motivo'] ?? null);

        return redirect()->route('citas.show', $cita)->with('ok', "Cita reprogramada para el {$cita->fecha->format('d/m/Y')} a las {$cita->hora}.");
    }
}
