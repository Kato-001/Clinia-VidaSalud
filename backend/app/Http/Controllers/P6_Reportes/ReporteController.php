<?php

namespace App\Http\Controllers\P6_Reportes;

use App\Http\Controllers\Controller;
use App\Models\Bitacora;
use App\Models\Cita;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

/** CU20 Generar reportes de citas */
class ReporteController extends Controller
{
    public function index(Request $request)
    {
        [$desde, $hasta] = $this->rango($request);
        $base = fn () => Cita::query()->whereBetween('fecha', [$desde, $hasta]);

        return view('P6_Reportes.CU20_GenerarReportes.index', [
            'desde' => $desde,
            'hasta' => $hasta,
            'total' => $base()->count(),
            'porEstado' => $base()->join('estadocita as e', 'e.codigo', '=', 'cita.codigoestado')
                ->select('e.codigo', 'e.nombre', DB::raw('count(*) as total'))->groupBy('e.codigo', 'e.nombre')->orderByDesc('total')->get(),
            'porEspecialidad' => $base()->join('especialidad as s', 's.idespecialidad', '=', 'cita.idespecialidad')
                ->select('s.nombre', DB::raw('count(*) as total'))->groupBy('s.nombre')->orderByDesc('total')->get(),
            'porMedico' => $base()->join('medico as m', 'm.idmedico', '=', 'cita.idmedico')
                ->select(DB::raw("m.nombres || ' ' || m.apellidos as nombre"), DB::raw('count(*) as total'),
                    DB::raw("sum(case when cita.codigoestado = 'ATENDIDA' then 1 else 0 end) as atendidas"),
                    DB::raw("sum(case when cita.codigoestado = 'NO_ASISTIO' then 1 else 0 end) as inasistencias"))
                ->groupBy('m.nombres', 'm.apellidos')->orderByDesc('total')->get(),
        ]);
    }

    /** Exporta las citas del rango en CSV (se abre en Excel) */
    public function exportar(Request $request): StreamedResponse
    {
        [$desde, $hasta] = $this->rango($request);
        Bitacora::registrar('EXPORTAR_REPORTE', 'REPORTES', 'cita', null, "Exportó el reporte de citas del {$desde} al {$hasta}");

        return response()->streamDownload(function () use ($desde, $hasta) {
            $salida = fopen('php://output', 'w');
            fwrite($salida, "\xEF\xBB\xBF");
            fputcsv($salida, ['Código', 'Fecha', 'Horario', 'Paciente', 'CI', 'Médico', 'Especialidad', 'Modalidad', 'Estado'], ';');
            Cita::with(['paciente', 'medico', 'especialidad', 'estado'])->whereBetween('fecha', [$desde, $hasta])
                ->orderBy('fecha')->orderBy('horainicio')
                ->each(fn ($c) => fputcsv($salida, [
                    $c->codigo, $c->fecha->format('d/m/Y'), $c->horario, $c->paciente->nombre_completo, $c->paciente->ci,
                    $c->medico->nombre_completo, $c->especialidad->nombre, $c->modalidad, $c->estado->nombre,
                ], ';'));
            fclose($salida);
        }, "reporte-citas-{$desde}-al-{$hasta}.csv", ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** Rango de fechas; por defecto el mes actual */
    private function rango(Request $request): array
    {
        $request->validate([
            'desde' => ['nullable', 'date_format:Y-m-d'],
            'hasta' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:desde'],
        ]);

        return [
            $request->input('desde', Carbon::today()->startOfMonth()->toDateString()),
            $request->input('hasta', Carbon::today()->endOfMonth()->toDateString()),
        ];
    }
}
