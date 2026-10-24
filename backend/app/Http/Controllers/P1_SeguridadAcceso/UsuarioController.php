<?php

namespace App\Http\Controllers\P1_SeguridadAcceso;

use App\Http\Controllers\Controller;
use App\Models\Bitacora;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/** CU3 Gestionar usuarios */
class UsuarioController extends Controller
{
    public function index(Request $request)
    {
        $usuarios = Usuario::with('rol')
            ->when($request->q, fn ($q, $t) => $q->where(fn ($w) => $w->where('nombre', 'ilike', "%{$t}%")->orWhere('username', 'ilike', "%{$t}%")->orWhere('email', 'ilike', "%{$t}%")))
            ->when($request->rol, fn ($q, $r) => $q->where('idrol', $r))
            ->orderBy('nombre')->paginate(15)->withQueryString();

        return view('P1_SeguridadAcceso.CU3_GestionarUsuarios.index', ['usuarios' => $usuarios, 'roles' => Rol::orderBy('nombre')->get()]);
    }

    public function create()
    {
        return view('P1_SeguridadAcceso.CU3_GestionarUsuarios.form', ['usuario' => new Usuario(), 'roles' => Rol::orderBy('nombre')->get()]);
    }

    public function store(Request $request)
    {
        $usuario = Usuario::create($this->validar($request) + ['estado' => 'ACTIVO']);
        Bitacora::registrar('CREAR', 'SEGURIDAD', 'usuario', $usuario->idusuario, "Creó el usuario {$usuario->username}");

        return redirect()->route('usuarios.index')->with('ok', "Usuario {$usuario->username} creado.");
    }

    public function edit(Usuario $usuario)
    {
        return view('P1_SeguridadAcceso.CU3_GestionarUsuarios.form', ['usuario' => $usuario, 'roles' => Rol::orderBy('nombre')->get()]);
    }

    public function update(Request $request, Usuario $usuario)
    {
        $datos = $this->validar($request, $usuario);
        if (empty($datos['password'])) {
            unset($datos['password']);
        }
        $usuario->update($datos);
        Bitacora::registrar('ACTUALIZAR', 'SEGURIDAD', 'usuario', $usuario->idusuario, "Actualizó el usuario {$usuario->username}");

        return redirect()->route('usuarios.index')->with('ok', 'Cambios guardados.');
    }

    /** Activar / desactivar la cuenta (no se borran usuarios: la bitácora los referencia) */
    public function estado(Request $request, Usuario $usuario)
    {
        if ($usuario->is($request->user())) {
            return back()->with('error', 'No puede desactivar su propia cuenta.');
        }
        $usuario->update(['estado' => $usuario->estado === 'ACTIVO' ? 'INACTIVO' : 'ACTIVO']);
        Bitacora::registrar($usuario->estado === 'ACTIVO' ? 'ACTIVAR' : 'DESACTIVAR', 'SEGURIDAD', 'usuario', $usuario->idusuario, "Cambió a {$usuario->estado} la cuenta {$usuario->username}");

        return back()->with('ok', "La cuenta de {$usuario->nombre} quedó " . mb_strtolower($usuario->estado) . '.');
    }

    private function validar(Request $request, ?Usuario $usuario = null): array
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'min:3', 'max:150'],
            'username' => ['required', 'string', 'min:3', 'max:50', 'regex:/^[A-Za-z0-9._-]+$/', Rule::unique('usuario', 'username')->ignore($usuario?->idusuario, 'idusuario')],
            'email' => ['required', 'email', 'max:150', Rule::unique('usuario', 'email')->ignore($usuario?->idusuario, 'idusuario')],
            'idrol' => ['required', 'exists:rol,idrol'],
            'password' => [$usuario ? 'nullable' : 'required', 'confirmed', Password::min(8)->letters()->numbers()],
        ]);
        $datos['username'] = mb_strtolower($datos['username']);
        $datos['email'] = mb_strtolower($datos['email']);

        return $datos;
    }
}
