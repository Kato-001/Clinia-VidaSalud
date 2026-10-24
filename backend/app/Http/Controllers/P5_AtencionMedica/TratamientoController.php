<?php

namespace App\Http\Controllers\P5_AtencionMedica;

use App\Http\Controllers\Controller;
use App\Models\Consulta;
use App\Models\Tratamiento;
use App\Services\AtencionService;
use Illuminate\Http\Request;

/** CU17 Registrar tratamiento (receta) */
class TratamientoController extends Controller
{
    public function __construct(private AtencionService $atencion)
    {
    }

    public function store(Request $request, Consulta $consulta)
    {
        $datos = $request->validate([
            'idmedicamento' => ['required', 'exists:medicamento,idmedicamento'],
            'dosis' => ['required', 'string', 'max:100'],
            'frecuencia' => ['required', 'string', 'max:100'],
            'duracion' => ['required', 'string', 'max:100'],
            'indicaciones' => ['nullable', 'string', 'max:2000'],
        ]);
        $this->atencion->recetar($consulta, $request->user()->medico, $datos);

        return back()->with('ok', 'Medicamento agregado a la receta.');
    }

    public function destroy(Request $request, Tratamiento $tratamiento)
    {
        $this->atencion->suspender($tratamiento, $request->user()->medico);

        return back()->with('ok', 'Medicamento quitado de la receta.');
    }
}
