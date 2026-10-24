<?php

namespace App\Http\Controllers\P1_SeguridadAcceso;

use App\Http\Controllers\Controller;
use App\Models\Bitacora;
use App\Models\Rol;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** CU3 Gestionar usuarios: roles (ROL.asignarPermisos / quitarPermisos) */
class RolController extends Controller
{
    /** Roles usados por los permisos del sistema: no se renombran ni eliminan */
    private const DEL_SISTEMA = [Rol::ADMINISTRADOR, Rol::RECEPCIONISTA, Rol::MEDICO, Rol::PACIENTE];

    public function index()
    {
        return view('P1_SeguridadAcceso.CU3_GestionarUsuarios.roles', [
            'roles' => Rol::withCount('usuarios')->orderBy('idrol')->get(),
            'delSistema' => self::DEL_SISTEMA,
        ]);
    }

    public function store(Request $request)
    {
        $rol = Rol::create($this->validar($request));
        Bitacora::registrar('CREAR', 'SEGURIDAD', 'rol', $rol->idrol, "Creó el rol {$rol->nombre}");

        return back()->with('ok', "Rol {$rol->nombre} creado.");
    }

    public function update(Request $request, Rol $rol)
    {
        $datos = $this->validar($request, $rol);
        if (in_array($rol->nombre, self::DEL_SISTEMA, true)) {
            $datos['nombre'] = $rol->nombre;
        }
        $rol->update($datos);
        Bitacora::registrar('ACTUALIZAR', 'SEGURIDAD', 'rol', $rol->idrol, "Actualizó el rol {$rol->nombre}");

        return back()->with('ok', 'Rol actualizado.');
    }

    public function destroy(Rol $rol)
    {
        if (in_array($rol->nombre, self::DEL_SISTEMA, true) || $rol->usuarios()->exists()) {
            return back()->with('error', 'No se puede eliminar un rol del sistema o que tiene usuarios asignados.');
        }
        $rol->delete();
        Bitacora::registrar('ELIMINAR', 'SEGURIDAD', 'rol', $rol->idrol, "Eliminó el rol {$rol->nombre}");

        return back()->with('ok', 'Rol eliminado.');
    }

    private function validar(Request $request, ?Rol $rol = null): array
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:50', 'regex:/^[A-Za-zÁÉÍÓÚáéíóúÑñ_ ]+$/u', Rule::unique('rol', 'nombre')->ignore($rol?->idrol, 'idrol')],
            'descripcion' => ['nullable', 'string', 'max:255'],
        ]);
        $datos['nombre'] = mb_strtoupper(str_replace(' ', '_', trim($datos['nombre'])));

        return $datos;
    }
}
