<?php

namespace App\Http\Controllers\P5_AtencionMedica;

use App\Http\Controllers\Controller;
use App\Models\Bitacora;
use App\Models\Medicamento;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** CU17 Registrar tratamiento: catálogo de medicamentos (administrador) */
class MedicamentoController extends Controller
{
    public function index(Request $request)
    {
        $medicamentos = Medicamento::when($request->q, fn ($q, $t) => $q->where('nombre', 'ilike', "%{$t}%"))
            ->orderBy('nombre')->paginate(15)->withQueryString();

        return view('P5_AtencionMedica.CU17_RegistrarTratamiento.medicamentos', [
            'medicamentos' => $medicamentos,
            'editar' => Medicamento::find($request->editar),
        ]);
    }

    public function store(Request $request)
    {
        $medicamento = Medicamento::create($this->validar($request));
        Bitacora::registrar('CREAR', 'ATENCION', 'medicamento', $medicamento->idmedicamento, "Registró el medicamento {$medicamento->nombre}");

        return redirect()->route('medicamentos.index')->with('ok', "Medicamento {$medicamento->nombre} registrado.");
    }

    public function update(Request $request, Medicamento $medicamento)
    {
        $medicamento->update($this->validar($request));
        Bitacora::registrar('ACTUALIZAR', 'ATENCION', 'medicamento', $medicamento->idmedicamento, "Actualizó el medicamento {$medicamento->nombre}");

        return redirect()->route('medicamentos.index')->with('ok', 'Cambios guardados.');
    }

    private function validar(Request $request): array
    {
        return $request->validate([
            'nombre' => ['required', 'string', 'max:150'],
            'presentacion' => ['nullable', 'string', 'max:100'],
            'estado' => ['required', Rule::in(['ACTIVO', 'INACTIVO'])],
        ]);
    }
}
