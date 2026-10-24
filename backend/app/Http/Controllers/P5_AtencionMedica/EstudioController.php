<?php

namespace App\Http\Controllers\P5_AtencionMedica;

use App\Http\Controllers\Controller;
use App\Models\Consulta;
use App\Models\Estudio;
use App\Services\AtencionService;
use Illuminate\Http\Request;

/** CU18 Gestionar estudios médicos (solicitar y registrar resultados) */
class EstudioController extends Controller
{
    public function __construct(private AtencionService $atencion)
    {
    }

    public function index(Request $request)
    {
        $pendientes = $request->input('ver', 'pendientes') === 'pendientes';
        $estudios = Estudio::with(['catalogo', 'consulta.cita.paciente'])
            ->whereHas('consulta.cita', fn ($q) => $q->where('idmedico', $request->user()->medico->idmedico))
            ->when($pendientes, fn ($q) => $q->where('estado', 'SOLICITADO'))
            ->orderByDesc('fechasolicitud')->paginate(15)->withQueryString();

        return view('P5_AtencionMedica.CU18_GestionarEstudiosMedicos.index', compact('estudios', 'pendientes'));
    }

    public function store(Request $request, Consulta $consulta)
    {
        $datos = $request->validate([
            'idcatalogo' => ['required', 'exists:catalogoestudio,idcatalogo'],
            'indicaciones' => ['nullable', 'string', 'max:2000'],
        ]);
        $this->atencion->solicitarEstudio($consulta, $request->user()->medico, $datos);

        return back()->with('ok', 'Estudio solicitado.');
    }

    public function resultado(Request $request, Estudio $estudio)
    {
        $this->exigirPropio($request, $estudio);
        $estudio->load(['catalogo', 'consulta.cita.paciente']);

        return view('P5_AtencionMedica.CU18_GestionarEstudiosMedicos.resultado', compact('estudio'));
    }

    public function guardarResultado(Request $request, Estudio $estudio)
    {
        $this->exigirPropio($request, $estudio);
        $datos = $request->validate([
            'resultado' => ['required', 'string', 'max:5000'],
            'archivoresultado' => ['nullable', 'url:http,https', 'max:500'],
        ], ['archivoresultado.url' => 'El archivo debe ser un enlace válido (http:// o https://).']);
        $this->atencion->registrarResultado($estudio, $datos);

        return redirect()->route('estudios.index')->with('ok', 'Resultado registrado. Se notificó al paciente.');
    }

    private function exigirPropio(Request $request, Estudio $estudio): void
    {
        abort_unless($estudio->consulta->cita->idmedico === $request->user()->medico?->idmedico, 404);
    }
}
