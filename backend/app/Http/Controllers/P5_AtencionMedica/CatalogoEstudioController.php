<?php

namespace App\Http\Controllers\P5_AtencionMedica;

use App\Http\Controllers\Controller;
use App\Models\Bitacora;
use App\Models\CatalogoEstudio;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** CU18 Gestionar estudios médicos: catálogo de estudios (administrador) */
class CatalogoEstudioController extends Controller
{
    public function index(Request $request)
    {
        $estudios = CatalogoEstudio::when($request->q, fn ($q, $t) => $q->where(fn ($w) => $w->where('nombre', 'ilike', "%{$t}%")->orWhere('categoria', 'ilike', "%{$t}%")))
            ->orderBy('categoria')->orderBy('nombre')->paginate(15)->withQueryString();

        return view('P5_AtencionMedica.CU18_GestionarEstudiosMedicos.catalogo', [
            'estudios' => $estudios,
            'editar' => CatalogoEstudio::find($request->editar),
        ]);
    }

    public function store(Request $request)
    {
        $estudio = CatalogoEstudio::create($this->validar($request));
        Bitacora::registrar('CREAR', 'ATENCION', 'catalogoestudio', $estudio->idcatalogo, "Agregó {$estudio->nombre} al catálogo de estudios");

        return redirect()->route('catalogo-estudios.index')->with('ok', "Estudio {$estudio->nombre} registrado.");
    }

    public function update(Request $request, CatalogoEstudio $catalogo)
    {
        $catalogo->update($this->validar($request));
        Bitacora::registrar('ACTUALIZAR', 'ATENCION', 'catalogoestudio', $catalogo->idcatalogo, "Actualizó {$catalogo->nombre} del catálogo de estudios");

        return redirect()->route('catalogo-estudios.index')->with('ok', 'Cambios guardados.');
    }

    private function validar(Request $request): array
    {
        return $request->validate([
            'nombre' => ['required', 'string', 'max:150'],
            'categoria' => ['nullable', 'string', 'max:100'],
            'descripcion' => ['nullable', 'string', 'max:255'],
            'estado' => ['required', Rule::in(['ACTIVO', 'INACTIVO'])],
        ]);
    }
}
