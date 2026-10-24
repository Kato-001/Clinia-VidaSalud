<?php

namespace App\Http\Controllers\P4_GestionCitas;

use App\Http\Controllers\Controller;
use App\Models\Cita;
use App\Services\CitaService;
use Illuminate\Http\Request;

/** CU12 Cancelar cita */
class CancelacionController extends Controller
{
    public function __construct(private CitaService $citas)
    {
    }

    public function formulario(Request $request, Cita $cita)
    {
        abort_unless($this->citas->puedeAcceder($request->user(), $cita), 404);
        $cita->load(['paciente', 'medico', 'especialidad']);

        return view('P4_GestionCitas.CU12_CancelarCita.cancelar', compact('cita'));
    }

    public function cancelar(Request $request, Cita $cita)
    {
        abort_unless($this->citas->puedeAcceder($request->user(), $cita), 404);
        $datos = $request->validate(['motivo' => ['required', 'string', 'min:3', 'max:255']]);
        $this->citas->cancelar($cita, $datos['motivo']);

        return redirect()->route('citas.show', $cita)->with('ok', "La cita {$cita->codigo} fue cancelada y el horario quedó libre.");
    }
}
