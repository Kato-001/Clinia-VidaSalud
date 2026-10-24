<?php

namespace App\Http\Controllers\P1_SeguridadAcceso;

use App\Http\Controllers\Controller;
use App\Models\Bitacora;
use App\Models\Configuracion;
use Illuminate\Http\Request;

/** Parámetros del sistema (CONFIGURACION.obtener / actualizar) */
class ConfiguracionController extends Controller
{
    public function index()
    {
        return view('P1_SeguridadAcceso.CU3_GestionarUsuarios.configuracion', ['parametros' => Configuracion::orderBy('clave')->get()]);
    }

    /** CONFIGURACION.actualizar() */
    public function update(Request $request)
    {
        $datos = $request->validate([
            'valores' => ['required', 'array'],
            'valores.*' => ['required', 'string', 'max:255'],
            'valores.DURACION_CITA_MINUTOS' => ['sometimes', 'integer', 'min:5', 'max:120'],
            'valores.HORAS_MIN_CANCELACION' => ['sometimes', 'integer', 'min:0', 'max:72'],
            'valores.DIAS_MAX_RESERVA' => ['sometimes', 'integer', 'min:1', 'max:365'],
        ], [], [
            'valores.DURACION_CITA_MINUTOS' => 'duración de la ficha',
            'valores.HORAS_MIN_CANCELACION' => 'horas mínimas para cancelar',
            'valores.DIAS_MAX_RESERVA' => 'días máximos de reserva',
        ]);

        foreach ($datos['valores'] as $clave => $valor) {
            Configuracion::whereKey($clave)->update(['valor' => $valor]);
        }
        Bitacora::registrar('ACTUALIZAR', 'SEGURIDAD', 'configuracion', null, 'Actualizó parámetros: ' . implode(', ', array_keys($datos['valores'])));

        return back()->with('ok', 'Configuración guardada.');
    }
}
