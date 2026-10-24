<?php

namespace App\Http\Controllers\P5_AtencionMedica;

use App\Http\Controllers\Controller;
use App\Models\Consulta;
use App\Models\Estudio;
use App\Models\HistoriaClinica;
use App\Models\Notificacion;
use Illuminate\Http\Request;

/** CU19 Consultar historial y resultados (PACIENTE.verHistorial, verResultados) y notificaciones */
class ResultadoController extends Controller
{
    public function historial(Request $request)
    {
        $paciente = $request->user()->paciente->load('historiaClinica');

        return view('P5_AtencionMedica.CU19_ConsultarHistorialResultados.historial', [
            'paciente' => $paciente,
            'antecedentes' => HistoriaClinica::ANTECEDENTES,
            'consultas' => Consulta::with(['cita.medico', 'cita.especialidad', 'tratamientos.medicamento', 'estudios.catalogo'])
                ->whereHas('cita', fn ($q) => $q->where('idpaciente', $paciente->idpaciente))
                ->orderByDesc('fechahora')->get(),
        ]);
    }

    public function resultados(Request $request)
    {
        $idpaciente = $request->user()->paciente->idpaciente;

        return view('P5_AtencionMedica.CU19_ConsultarHistorialResultados.resultados', [
            'estudios' => Estudio::with(['catalogo', 'consulta.cita.medico'])
                ->whereHas('consulta.cita', fn ($q) => $q->where('idpaciente', $idpaciente))
                ->orderByDesc('fechasolicitud')->get(),
        ]);
    }

    /** Notificaciones del paciente (las nuevas se resaltan hasta marcarlas como leídas) */
    public function notificaciones(Request $request)
    {
        $notificaciones = Notificacion::dePaciente($request->user()->paciente->idpaciente)
            ->with('cita')->orderByDesc('idnotificacion')->paginate(20);

        return view('P5_AtencionMedica.CU19_ConsultarHistorialResultados.notificaciones', compact('notificaciones'));
    }

    /** NOTIFICACION.marcarLeida() para todas las pendientes */
    public function marcarTodas(Request $request)
    {
        Notificacion::dePaciente($request->user()->paciente->idpaciente)->whereNull('leidaen')->update(['leidaen' => now()]);

        return back()->with('ok', 'Todas las notificaciones quedaron como leídas.');
    }
}
