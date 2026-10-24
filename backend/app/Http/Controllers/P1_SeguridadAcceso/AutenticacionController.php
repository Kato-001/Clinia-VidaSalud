<?php

namespace App\Http\Controllers\P1_SeguridadAcceso;

use App\Http\Controllers\Controller;
use App\Models\Bitacora;
use App\Models\Paciente;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

/** CU1 Iniciar sesión (USUARIO.iniciarSesion, cerrarSesion, cambiarPassword) */
class AutenticacionController extends Controller
{
    public function formulario()
    {
        return view('P1_SeguridadAcceso.CU1_IniciarSesion.login');
    }

    /** Acepta usuario, correo o CI (pacientes) */
    public function ingresar(Request $request)
    {
        $datos = $request->validate([
            'login' => ['required', 'string', 'max:150'],
            'password' => ['required', 'string', 'max:100'],
        ]);
        $login = mb_strtolower(trim($datos['login']));

        $usuario = Usuario::whereRaw('LOWER(username) = ?', [$login])->orWhereRaw('LOWER(email) = ?', [$login])->first()
            ?? Paciente::where('ci', $datos['login'])->first()?->usuario;

        if (! $usuario || ! Hash::check($datos['password'], $usuario->password)) {
            return back()->withInput($request->only('login'))->withErrors(['login' => 'Usuario o contraseña incorrectos.']);
        }
        if ($usuario->estado !== 'ACTIVO') {
            return back()->withInput($request->only('login'))->withErrors(['login' => 'Su cuenta está inactiva. Comuníquese con la clínica.']);
        }

        Auth::login($usuario);
        $request->session()->regenerate();
        $usuario->update(['ultimoacceso' => now()]);
        Bitacora::registrar('INICIAR_SESION', 'SEGURIDAD', 'usuario', $usuario->idusuario, "Inicio de sesión de {$usuario->username}");

        return redirect()->intended(route('dashboard'));
    }

    public function salir(Request $request)
    {
        Bitacora::registrar('CERRAR_SESION', 'SEGURIDAD', 'usuario', $request->user()->idusuario, 'Cierre de sesión');
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('ok', 'Sesión cerrada correctamente.');
    }

    public function editarPassword()
    {
        return view('P1_SeguridadAcceso.CU1_IniciarSesion.password');
    }

    public function cambiarPassword(Request $request)
    {
        $request->validate([
            'actual' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', 'different:actual', Password::min(8)->letters()->numbers()],
        ]);
        $usuario = $request->user();
        $usuario->update(['password' => $request->input('password')]);
        Bitacora::registrar('CAMBIAR_PASSWORD', 'SEGURIDAD', 'usuario', $usuario->idusuario, 'Cambio de contraseña');

        return back()->with('ok', 'Su contraseña fue actualizada.');
    }
}
