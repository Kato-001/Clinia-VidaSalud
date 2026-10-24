<?php

namespace App\Http\Controllers\P3_GestionPacientes;

use App\Http\Controllers\Controller;
use App\Services\PacienteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

/** CU8 Registrarse como paciente (PACIENTE.registrarse) */
class RegistroController extends Controller
{
    use ReglasPaciente;

    public function formulario()
    {
        return view('P3_GestionPacientes.CU8_RegistrarsePaciente.registro');
    }

    public function guardar(Request $request, PacienteService $servicio)
    {
        $datos = $request->validate($this->reglasPaciente() + [
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
            'acepta' => ['accepted'],
        ]);
        $datos['aceptanotificaciones'] = $request->boolean('aceptanotificaciones');

        $paciente = $servicio->registrar($datos);
        Auth::login($paciente->usuario);
        $request->session()->regenerate();

        return redirect()->route('dashboard')->with('ok', "¡Bienvenido(a)! Su código de paciente es {$paciente->codigo}. Para ingresar use su CI o su correo.");
    }
}
