<?php

namespace App\Http\Controllers\P5_AtencionMedica;

use App\Http\Controllers\Controller;
use App\Models\Consulta;
use App\Models\HistoriaClinica;
use App\Models\Paciente;
use App\Services\AtencionService;
use Illuminate\Http\Request;

/** CU16 Gestionar historial clínico (HISTORIACLINICA.consultar / actualizarAntecedentes) */
class HistoriaClinicaController extends Controller
{
    public function show(Request $request, Paciente $paciente)
    {
        $this->exigirPaciente($request, $paciente);
        $paciente->load(['historiaClinica', 'contactos']);

        return view('P5_AtencionMedica.CU16_GestionarHistorialClinico.historia', [
            'paciente' => $paciente,
            'antecedentes' => HistoriaClinica::ANTECEDENTES,
            'consultas' => Consulta::with(['cita.medico', 'cita.especialidad', 'tratamientos.medicamento', 'estudios.catalogo'])
                ->whereHas('cita', fn ($q) => $q->where('idpaciente', $paciente->idpaciente))
                ->orderByDesc('fechahora')->get(),
        ]);
    }

    public function update(Request $request, Paciente $paciente, AtencionService $atencion)
    {
        $this->exigirPaciente($request, $paciente);
        $datos = $request->validate(array_fill_keys(array_keys(HistoriaClinica::ANTECEDENTES), ['nullable', 'string', 'max:5000']));
        $atencion->actualizarAntecedentes($paciente, $datos);

        return back()->with('ok', 'Historia clínica actualizada.');
    }

    /** Solo médicos que atendieron o tienen citas con el paciente */
    private function exigirPaciente(Request $request, Paciente $paciente): void
    {
        abort_unless($paciente->citas()->where('idmedico', $request->user()->medico?->idmedico)->exists(), 403);
    }
}
