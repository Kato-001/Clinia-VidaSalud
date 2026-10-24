<?php

namespace App\Services;

use App\Models\Bitacora;
use App\Models\Paciente;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;

/**
 * CU7 Gestionar pacientes y CU8 Registrarse: un paciente siempre tiene su
 * USUARIO (rol PACIENTE, usuario = CI) y su HISTORIACLINICA.
 */
class PacienteService
{
    /**
     * PACIENTE.registrarse() o registro por recepción.
     *
     * @param  array  $datos  datos de PACIENTE + password (+ alergias y contacto de emergencia opcionales)
     */
    public function registrar(array $datos): Paciente
    {
        return DB::transaction(function () use ($datos) {
            $usuario = Usuario::create([
                'nombre' => "{$datos['nombres']} {$datos['apellidos']}",
                'username' => $datos['ci'],
                'email' => $datos['email'],
                'password' => $datos['password'],
                'estado' => 'ACTIVO',
                'idrol' => Rol::where('nombre', Rol::PACIENTE)->value('idrol'),
            ]);

            $paciente = Paciente::create([
                'codigo' => 'TMP-' . bin2hex(random_bytes(6)),
                'idusuario' => $usuario->idusuario,
                'estado' => 'ACTIVO',
            ] + $this->datosPaciente($datos));
            $numero = str_pad((string) $paciente->idpaciente, 6, '0', STR_PAD_LEFT);
            $paciente->update(['codigo' => "PAC-{$numero}"]);

            $paciente->historiaClinica()->create(['numero' => "HC-{$numero}", 'alergias' => $datos['alergias'] ?? null]);
            $this->guardarContacto($paciente, $datos);

            Bitacora::registrar('REGISTRAR_PACIENTE', 'PACIENTES', 'paciente', $paciente->idpaciente,
                "Registro del paciente {$paciente->nombre_completo} (CI {$paciente->ci})", auth()->user()?->idusuario ?? $usuario->idusuario);

            return $paciente;
        });
    }

    /** PACIENTE.actualizarDatos() */
    public function actualizar(Paciente $paciente, array $datos): void
    {
        DB::transaction(function () use ($paciente, $datos) {
            $paciente->update($this->datosPaciente($datos));
            // La cuenta del paciente usa su CI como usuario y el mismo correo
            $paciente->usuario->update(['nombre' => $paciente->nombre_completo, 'username' => $paciente->ci, 'email' => $paciente->email]);
            if (array_key_exists('alergias', $datos)) {
                $paciente->historiaClinica()->updateOrCreate(
                    ['idpaciente' => $paciente->idpaciente],
                    ['alergias' => $datos['alergias'], 'numero' => $paciente->historiaClinica?->numero ?? 'HC-' . str_pad((string) $paciente->idpaciente, 6, '0', STR_PAD_LEFT)],
                );
            }
            $this->guardarContacto($paciente, $datos);
            Bitacora::registrar('ACTUALIZAR_PACIENTE', 'PACIENTES', 'paciente', $paciente->idpaciente, "Actualizó los datos de {$paciente->nombre_completo}");
        });
    }

    /** Activa o desactiva al paciente y su cuenta (el historial se conserva) */
    public function cambiarEstado(Paciente $paciente): void
    {
        DB::transaction(function () use ($paciente) {
            $estado = $paciente->estado === 'ACTIVO' ? 'INACTIVO' : 'ACTIVO';
            $paciente->update(['estado' => $estado]);
            $paciente->usuario->update(['estado' => $estado]);
            Bitacora::registrar($estado === 'ACTIVO' ? 'ACTIVAR' : 'DESACTIVAR', 'PACIENTES', 'paciente', $paciente->idpaciente, "Cambió a {$estado} al paciente {$paciente->nombre_completo}");
        });
    }

    private function datosPaciente(array $datos): array
    {
        return array_intersect_key($datos, array_flip([
            'ci', 'nombres', 'apellidos', 'fechanacimiento', 'sexo', 'telefono', 'email', 'direccion', 'tiposangre', 'aceptanotificaciones',
        ]));
    }

    /** El formulario de recepción incluye un contacto de emergencia (CONTACTOEMERGENCIA.agregar / editar) */
    private function guardarContacto(Paciente $paciente, array $datos): void
    {
        if (empty($datos['contacto_nombre']) || empty($datos['contacto_telefono'])) {
            return;
        }
        $contacto = $paciente->contactos()->first();
        $valores = [
            'nombre' => $datos['contacto_nombre'],
            'parentesco' => ($datos['contacto_parentesco'] ?? null) ?: 'No indicado',
            'telefono' => $datos['contacto_telefono'],
        ];
        $contacto ? $contacto->update($valores) : $paciente->contactos()->create($valores);
    }
}
