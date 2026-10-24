<?php

namespace App\Services;

use App\Exceptions\ReglaNegocio;
use App\Models\Bitacora;
use App\Models\BloqueoAgenda;
use App\Models\Cita;
use App\Models\Configuracion;
use App\Models\Consulta;
use App\Models\EstadoCita;
use App\Models\HistorialCita;
use App\Models\Medico;
use App\Models\Notificacion;
use App\Models\Paciente;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Reglas de negocio de las citas (CU9 a CU14):
 * - Solo dentro del HORARIO activo del médico y fuera de sus BLOQUEOAGENDA.
 * - Sin dos citas del mismo médico (ni del mismo paciente) en el mismo horario.
 * - Cada cambio queda en HISTORIALCITA, las acciones en BITACORA y se notifica al paciente.
 */
class CitaService
{
    /** Duración de la ficha en minutos (CONFIGURACION.DURACION_CITA_MINUTOS) */
    public function duracion(): int
    {
        return Configuracion::numero('DURACION_CITA_MINUTOS', 15);
    }

    /** ¿El usuario puede ver o modificar esta cita? */
    public function puedeAcceder(Usuario $usuario, Cita $cita): bool
    {
        return match (true) {
            $usuario->esPersonal() => true,
            $usuario->tieneRol(Rol::MEDICO) => $cita->idmedico === $usuario->medico?->idmedico,
            $usuario->tieneRol(Rol::PACIENTE) => $cita->idpaciente === $usuario->paciente?->idpaciente,
            default => false,
        };
    }

    // ------------------------------------------------------------------
    // CU9 Consultar disponibilidad
    // ------------------------------------------------------------------

    /**
     * HORARIO.consultarDisponibilidad(): fichas del médico en una fecha.
     *
     * @return array{bloques: array<int, array{inicio: string, fin: string, disponible: bool}>, mensaje: ?string}
     */
    public function disponibilidad(Medico $medico, string $fecha): array
    {
        $dia = Carbon::parse($fecha);
        if ($medico->estado !== 'ACTIVO') {
            return ['bloques' => [], 'mensaje' => 'El médico no está atendiendo actualmente.'];
        }

        $horarios = $medico->horarios()->where('estado', 'ACTIVO')->where('diasemana', $dia->isoWeekday())->get();
        if ($horarios->isEmpty()) {
            return ['bloques' => [], 'mensaje' => 'El médico no atiende los ' . mb_strtolower($dia->locale('es')->dayName) . '.'];
        }

        $bloqueos = BloqueoAgenda::where('idmedico', $medico->idmedico)->whereDate('fecha', $fecha)->get();
        if ($bloqueos->contains(fn ($b) => $b->esDiaCompleto())) {
            return ['bloques' => [], 'mensaje' => 'El médico no atiende ese día (agenda bloqueada).'];
        }

        $ocupadas = Cita::where('idmedico', $medico->idmedico)->whereDate('fecha', $fecha)
            ->whereHas('estado', fn ($q) => $q->where('ocupahorario', true))
            ->get(['horainicio', 'horafin']);

        $duracion = $this->duracion();
        $ahora = now();
        $cruza = fn (int $a, int $b, string $x, string $y) => $a < $this->minutos($y) && $b > $this->minutos($x);
        $bloques = [];

        foreach ($horarios as $h) {
            for ($ini = $this->minutos($h->horainicio); $ini + $duracion <= $this->minutos($h->horafin); $ini += $duracion) {
                $fin = $ini + $duracion;
                $libre = $dia->copy()->setTime(intdiv($ini, 60), $ini % 60)->gt($ahora)
                    && ! $bloqueos->contains(fn ($b) => $cruza($ini, $fin, $b->horainicio, $b->horafin))
                    && ! $ocupadas->contains(fn ($c) => $cruza($ini, $fin, $c->horainicio, $c->horafin));
                $bloques[] = ['inicio' => $this->hora($ini), 'fin' => $this->hora($fin), 'disponible' => $libre];
            }
        }

        return ['bloques' => $bloques, 'mensaje' => null];
    }

    /**
     * Valida que el horario siga libre. Bloquea la fila del médico (FOR UPDATE)
     * para que dos reservas simultáneas no tomen la misma ficha.
     */
    public function validarDisponibilidad(Medico $medico, string $fecha, string $inicio, string $fin, ?int $idpaciente = null, ?int $excluirIdCita = null): void
    {
        $momento = Carbon::parse("{$fecha} {$inicio}");
        if ($momento->lte(now())) {
            throw new ReglaNegocio('No se puede reservar en una fecha u hora que ya pasó.');
        }
        $maximo = Configuracion::numero('DIAS_MAX_RESERVA', 60);
        if ($momento->gt(now()->addDays($maximo)->endOfDay())) {
            throw new ReglaNegocio("Solo se puede reservar con hasta {$maximo} días de anticipación.");
        }

        $bloqueado = Medico::whereKey($medico->idmedico)->lockForUpdate()->first();
        if (! $bloqueado || $bloqueado->estado !== 'ACTIVO') {
            throw new ReglaNegocio('El médico seleccionado no está disponible.');
        }

        $enHorario = $medico->horarios()->where('estado', 'ACTIVO')
            ->where('diasemana', $momento->isoWeekday())
            ->where('horainicio', '<=', $inicio)->where('horafin', '>=', $fin)->exists();
        if (! $enHorario) {
            throw new ReglaNegocio('La hora elegida está fuera del horario de atención del médico.');
        }

        $bloqueo = BloqueoAgenda::where('idmedico', $medico->idmedico)->whereDate('fecha', $fecha)
            ->where(fn ($q) => $q->whereNull('horainicio')->orWhere(fn ($r) => $r->where('horainicio', '<', $fin)->where('horafin', '>', $inicio)))
            ->first();
        if ($bloqueo) {
            throw new ReglaNegocio('El médico no atiende en ese horario (agenda bloqueada por ' . mb_strtolower(BloqueoAgenda::TIPOS[$bloqueo->tipo] ?? $bloqueo->tipo) . ').');
        }

        $cruce = fn ($q) => $q->whereDate('fecha', $fecha)->where('horainicio', '<', $fin)->where('horafin', '>', $inicio)
            ->whereHas('estado', fn ($e) => $e->where('ocupahorario', true))
            ->when($excluirIdCita, fn ($c) => $c->where('idcita', '!=', $excluirIdCita));

        if (Cita::where('idmedico', $medico->idmedico)->where($cruce)->exists()) {
            throw new ReglaNegocio('El médico ya tiene una cita en ese horario. Elija otra ficha.');
        }
        if ($idpaciente && Cita::where('idpaciente', $idpaciente)->where($cruce)->exists()) {
            throw new ReglaNegocio('El paciente ya tiene otra cita en ese mismo horario.');
        }
    }

    // ------------------------------------------------------------------
    // CU10 Reservar ficha
    // ------------------------------------------------------------------

    /** PACIENTE.reservarCita() / CITA.programar() */
    public function reservar(Paciente $paciente, array $datos): Cita
    {
        return DB::transaction(function () use ($paciente, $datos) {
            if ($paciente->estado !== 'ACTIVO') {
                throw new ReglaNegocio('El paciente está inactivo y no puede reservar citas.');
            }
            $medico = Medico::findOrFail($datos['idmedico']);
            $especialidad = $medico->especialidades()->where('especialidad.idespecialidad', $datos['idespecialidad'])
                ->where('especialidad.estado', 'ACTIVO')->first();
            if (! $especialidad) {
                throw new ReglaNegocio('El médico no atiende la especialidad seleccionada.');
            }

            $inicio = substr($datos['horainicio'], 0, 5);
            $fin = $this->hora($this->minutos($inicio) + $this->duracion());
            $this->validarDisponibilidad($medico, $datos['fecha'], $inicio, $fin, $paciente->idpaciente);

            $cita = Cita::create([
                'codigo' => 'TMP-' . bin2hex(random_bytes(6)),
                'fecha' => $datos['fecha'],
                'horainicio' => $inicio,
                'horafin' => $fin,
                'modalidad' => $datos['modalidad'] ?? 'PRESENCIAL',
                'motivo' => $datos['motivo'] ?? null,
                'idpaciente' => $paciente->idpaciente,
                'idmedico' => $medico->idmedico,
                'idespecialidad' => $especialidad->idespecialidad,
                'codigoestado' => EstadoCita::PROGRAMADA,
            ]);
            $cita->update(['codigo' => 'CIT-' . str_pad((string) $cita->idcita, 6, '0', STR_PAD_LEFT)]);

            HistorialCita::registrarCambio($cita, 'RESERVA', ['fechanueva' => $datos['fecha'], 'horanueva' => $inicio, 'motivo' => $cita->motivo]);
            Notificacion::programar('RESERVA', "Su cita {$cita->codigo} de {$especialidad->nombre} con {$medico->nombre_completo} fue reservada para el "
                . Carbon::parse($datos['fecha'])->format('d/m/Y') . " a las {$inicio}.", $cita->idcita);
            Bitacora::registrar('RESERVAR_CITA', 'CITAS', 'cita', $cita->idcita, "Reservó la cita {$cita->codigo} ({$datos['fecha']} {$inicio}) con {$medico->nombre_completo}");

            return $cita;
        });
    }

    /** PACIENTE.confirmarCita() / CITA.confirmar() */
    public function confirmar(Cita $cita): void
    {
        DB::transaction(function () use ($cita) {
            $cita = Cita::whereKey($cita->idcita)->lockForUpdate()->firstOrFail();
            if (! in_array($cita->codigoestado, [EstadoCita::PROGRAMADA, EstadoCita::REPROGRAMADA], true)) {
                throw new ReglaNegocio('Solo se pueden confirmar citas programadas o reprogramadas.');
            }
            $cita->update(['codigoestado' => EstadoCita::CONFIRMADA]);
            HistorialCita::registrarCambio($cita, 'CONFIRMACION');
            Bitacora::registrar('CONFIRMAR_CITA', 'CITAS', 'cita', $cita->idcita, "Confirmó la cita {$cita->codigo}");
        });
    }

    // ------------------------------------------------------------------
    // CU11 Reprogramar cita
    // ------------------------------------------------------------------

    /** PACIENTE.reprogramarCita() / CITA.reprogramar() */
    public function reprogramar(Cita $cita, string $fecha, string $horainicio, ?string $motivo): Cita
    {
        return DB::transaction(function () use ($cita, $fecha, $horainicio, $motivo) {
            $cita = Cita::whereKey($cita->idcita)->lockForUpdate()->firstOrFail();
            $this->exigirPendiente($cita, 'reprogramar');
            $this->exigirAnticipacion($cita, 'reprogramar');

            $inicio = substr($horainicio, 0, 5);
            if ($cita->dia === $fecha && $cita->hora === $inicio) {
                throw new ReglaNegocio('La nueva fecha y hora son iguales a las actuales.');
            }
            $fin = $this->hora($this->minutos($inicio) + $this->duracion());
            $this->validarDisponibilidad($cita->medico, $fecha, $inicio, $fin, $cita->idpaciente, $cita->idcita);

            $anterior = ['fecha' => $cita->dia, 'hora' => $cita->hora];
            $cita->update(['fecha' => $fecha, 'horainicio' => $inicio, 'horafin' => $fin, 'codigoestado' => EstadoCita::REPROGRAMADA]);

            HistorialCita::registrarCambio($cita, 'REPROGRAMACION', [
                'fechaanterior' => $anterior['fecha'], 'horaanterior' => $anterior['hora'],
                'fechanueva' => $fecha, 'horanueva' => $inicio, 'motivo' => $motivo,
            ]);
            Notificacion::programar('REPROGRAMACION', "Su cita {$cita->codigo} fue reprogramada del "
                . Carbon::parse($anterior['fecha'])->format('d/m/Y') . " {$anterior['hora']} al "
                . Carbon::parse($fecha)->format('d/m/Y') . " a las {$inicio}.", $cita->idcita);
            Bitacora::registrar('REPROGRAMAR_CITA', 'CITAS', 'cita', $cita->idcita, "Reprogramó la cita {$cita->codigo} de {$anterior['fecha']} {$anterior['hora']} a {$fecha} {$inicio}");

            return $cita;
        });
    }

    // ------------------------------------------------------------------
    // CU12 Cancelar cita
    // ------------------------------------------------------------------

    /** PACIENTE.cancelarCita() / CITA.cancelar() */
    public function cancelar(Cita $cita, ?string $motivo): void
    {
        DB::transaction(function () use ($cita, $motivo) {
            $cita = Cita::whereKey($cita->idcita)->lockForUpdate()->firstOrFail();
            $this->exigirPendiente($cita, 'cancelar');
            $this->exigirAnticipacion($cita, 'cancelar');

            $cita->update(['codigoestado' => EstadoCita::CANCELADA]);
            HistorialCita::registrarCambio($cita, 'CANCELACION', ['fechaanterior' => $cita->dia, 'horaanterior' => $cita->hora, 'motivo' => $motivo]);

            $quien = match (true) {
                auth()->user()?->tieneRol(Rol::PACIENTE) => '',
                auth()->user()?->tieneRol(Rol::MEDICO) => ' por el médico',
                default => ' por la clínica',
            };
            Notificacion::programar('CANCELACION', "Su cita {$cita->codigo} del {$cita->fecha->format('d/m/Y')} a las {$cita->hora} fue cancelada{$quien}."
                . ($motivo ? " Motivo: {$motivo}" : ''), $cita->idcita);
            Bitacora::registrar('CANCELAR_CITA', 'CITAS', 'cita', $cita->idcita, "Canceló la cita {$cita->codigo}" . ($motivo ? ": {$motivo}" : ''));
        });
    }

    // ------------------------------------------------------------------
    // CU13 Registrar asistencia
    // ------------------------------------------------------------------

    /** CITA.marcarNoAsistio() */
    public function marcarNoAsistio(Cita $cita): void
    {
        DB::transaction(function () use ($cita) {
            $cita = Cita::whereKey($cita->idcita)->lockForUpdate()->firstOrFail();
            $this->exigirPendiente($cita, 'marcar como no asistida');
            if ($cita->fecha->isAfter(today())) {
                throw new ReglaNegocio('Solo se puede registrar la inasistencia el día de la cita o después.');
            }
            $cita->update(['codigoestado' => EstadoCita::NO_ASISTIO]);
            HistorialCita::registrarCambio($cita, 'NO_ASISTIO');
            Bitacora::registrar('MARCAR_NO_ASISTIO', 'CITAS', 'cita', $cita->idcita, "Registró que el paciente no asistió a la cita {$cita->codigo}");
        });
    }

    /** MEDICO.atenderCita(): el paciente asistió; se abre la CONSULTA (CONSULTA.iniciar()) */
    public function atender(Cita $cita): Consulta
    {
        return DB::transaction(function () use ($cita) {
            $cita = Cita::whereKey($cita->idcita)->lockForUpdate()->firstOrFail();
            if ($cita->codigoestado === EstadoCita::EN_ATENCION) {
                return $cita->consulta;
            }
            $this->exigirPendiente($cita, 'atender');
            if ($cita->fecha->isAfter(today())) {
                throw new ReglaNegocio('Solo se pueden atender citas de hoy o de días anteriores.');
            }
            $cita->update(['codigoestado' => EstadoCita::EN_ATENCION]);
            $consulta = Consulta::firstOrCreate(['idcita' => $cita->idcita], ['fechahora' => now(), 'motivo' => $cita->motivo]);
            HistorialCita::registrarCambio($cita, 'INICIO_ATENCION');
            Bitacora::registrar('ATENDER_CITA', 'ATENCION', 'cita', $cita->idcita, "Registró la asistencia e inició la atención de la cita {$cita->codigo}");

            return $consulta;
        });
    }

    /** Cierra la atención: la cita queda ATENDIDA (lo usa AtencionService) */
    public function finalizar(Cita $cita): void
    {
        if ($cita->codigoestado !== EstadoCita::EN_ATENCION) {
            throw new ReglaNegocio('La cita no está en atención.');
        }
        $cita->update(['codigoestado' => EstadoCita::ATENDIDA]);
        HistorialCita::registrarCambio($cita, 'ATENCION_FINALIZADA');
    }

    // ------------------------------------------------------------------
    // Utilidades
    // ------------------------------------------------------------------

    private function exigirPendiente(Cita $cita, string $accion): void
    {
        if (! $cita->estaPendiente()) {
            $estado = EstadoCita::find($cita->codigoestado)?->nombre ?? $cita->codigoestado;
            throw new ReglaNegocio("No se puede {$accion} una cita en estado «" . mb_strtolower($estado) . '».');
        }
    }

    /** El paciente debe respetar la anticipación mínima (CONFIGURACION.HORAS_MIN_CANCELACION) */
    private function exigirAnticipacion(Cita $cita, string $accion): void
    {
        if (! auth()->user()?->tieneRol(Rol::PACIENTE)) {
            return;
        }
        $horas = Configuracion::numero('HORAS_MIN_CANCELACION', 2);
        if (Carbon::parse("{$cita->dia} {$cita->hora}")->lt(now()->addHours($horas))) {
            throw new ReglaNegocio("Solo puede {$accion} la cita con al menos {$horas} hora(s) de anticipación. Comuníquese con la clínica.");
        }
    }

    private function minutos(string $hora): int
    {
        [$h, $m] = explode(':', $hora);
        return (int) $h * 60 + (int) $m;
    }

    private function hora(int $minutos): string
    {
        return sprintf('%02d:%02d', intdiv($minutos, 60), $minutos % 60);
    }
}
