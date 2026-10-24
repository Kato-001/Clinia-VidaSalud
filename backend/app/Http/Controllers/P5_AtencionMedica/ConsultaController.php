<?php

namespace App\Http\Controllers\P5_AtencionMedica;

use App\Http\Controllers\Controller;
use App\Models\CatalogoEstudio;
use App\Models\Consulta;
use App\Models\EstadoCita;
use App\Models\Medicamento;
use App\Services\AtencionService;
use Illuminate\Http\Request;

/** CU15 Registrar consulta médica (signos vitales, examen físico, diagnóstico) */
class ConsultaController extends Controller
{
    public function __construct(private AtencionService $atencion)
    {
    }

    public function show(Request $request, Consulta $consulta)
    {
        abort_unless($consulta->cita->idmedico === $request->user()->medico?->idmedico, 404);
        $consulta->load(['cita.paciente.historiaClinica', 'cita.paciente.contactos', 'cita.especialidad', 'tratamientos.medicamento', 'estudios.catalogo']);
        $paciente = $consulta->cita->paciente;

        return view('P5_AtencionMedica.CU15_RegistrarConsultaMedica.consulta', [
            'consulta' => $consulta,
            'cita' => $consulta->cita,
            'paciente' => $paciente,
            'editable' => $consulta->cita->codigoestado === EstadoCita::EN_ATENCION,
            'medicamentos' => Medicamento::buscar(),
            'catalogo' => CatalogoEstudio::listar(),
            'anteriores' => Consulta::with('cita.especialidad', 'cita.medico')
                ->whereHas('cita', fn ($q) => $q->where('idpaciente', $paciente->idpaciente)->where('idcita', '!=', $consulta->idcita))
                ->orderByDesc('fechahora')->limit(5)->get(),
        ]);
    }

    public function update(Request $request, Consulta $consulta)
    {
        $datos = $request->validate([
            'motivo' => ['nullable', 'string', 'max:255'],
            'peso' => ['nullable', 'numeric', 'between:0.5,500'],
            'talla' => ['nullable', 'numeric', 'between:20,300'],
            'presionarterial' => ['nullable', 'regex:/^\d{2,3}\/\d{2,3}$/'],
            'temperatura' => ['nullable', 'numeric', 'between:30,45'],
            'frecuenciacardiaca' => ['nullable', 'integer', 'between:20,250'],
            'examenfisico' => ['nullable', 'string', 'max:5000'],
            'diagnostico' => ['nullable', 'string', 'max:5000'],
            'observaciones' => ['nullable', 'string', 'max:5000'],
        ], ['presionarterial.regex' => 'La presión arterial debe tener el formato 120/80.']);

        $this->atencion->registrarConsulta($consulta, $request->user()->medico, $datos);

        return back()->with('ok', 'Consulta guardada.');
    }

    /** CONSULTA.cerrar() */
    public function cerrar(Request $request, Consulta $consulta)
    {
        $this->atencion->cerrar($consulta, $request->user()->medico);

        return redirect()->route('agenda.index', ['fecha' => $consulta->cita->dia])->with('ok', "Consulta finalizada. La cita {$consulta->cita->codigo} quedó atendida.");
    }
}
