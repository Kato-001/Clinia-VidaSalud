<?php

namespace App\Http\Controllers\P3_GestionPacientes;

use App\Http\Controllers\Controller;
use App\Models\Bitacora;
use App\Models\ContactoEmergencia;
use App\Services\PacienteService;
use Illuminate\Http\Request;

/** El paciente actualiza sus datos y contactos de emergencia (PACIENTE.actualizarDatos, CONTACTOEMERGENCIA.agregar) */
class PerfilController extends Controller
{
    use ReglasPaciente;

    public function edit(Request $request)
    {
        $paciente = $request->user()->paciente->load(['contactos', 'historiaClinica']);

        return view('P3_GestionPacientes.CU7_GestionarPacientes.perfil', compact('paciente'));
    }

    public function update(Request $request, PacienteService $servicio)
    {
        $paciente = $request->user()->paciente;
        $reglas = collect($this->reglasPaciente($paciente))->except('ci')->all();
        $datos = $request->validate($reglas);
        $datos['aceptanotificaciones'] = $request->boolean('aceptanotificaciones');
        $servicio->actualizar($paciente, $datos);

        return back()->with('ok', 'Sus datos fueron actualizados.');
    }

    public function agregarContacto(Request $request)
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:150'],
            'parentesco' => ['required', 'string', 'max:50'],
            'telefono' => ['required', 'regex:/^[0-9+\s\-]{7,20}$/'],
        ]);
        $contacto = $request->user()->paciente->contactos()->create($datos);
        Bitacora::registrar('AGREGAR_CONTACTO', 'PACIENTES', 'contactoemergencia', $contacto->idcontacto, "Agregó el contacto de emergencia {$contacto->nombre}");

        return back()->with('ok', 'Contacto de emergencia agregado.');
    }

    public function eliminarContacto(Request $request, ContactoEmergencia $contacto)
    {
        abort_unless($contacto->idpaciente === $request->user()->paciente->idpaciente, 404);
        $contacto->delete();
        Bitacora::registrar('ELIMINAR_CONTACTO', 'PACIENTES', 'contactoemergencia', $contacto->idcontacto, "Eliminó el contacto de emergencia {$contacto->nombre}");

        return back()->with('ok', 'Contacto eliminado.');
    }
}
