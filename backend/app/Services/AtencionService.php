<?php

namespace App\Services;

use App\Exceptions\ReglaNegocio;
use App\Models\Bitacora;
use App\Models\BloqueoAgenda;
use App\Models\CatalogoEstudio;
use App\Models\Cita;
use App\Models\Consulta;
use App\Models\EstadoCita;
use App\Models\Estudio;
use App\Models\HistoriaClinica;
use App\Models\Medicamento;
use App\Models\Medico;
use App\Models\Notificacion;
use App\Models\Paciente;
use App\Models\Tratamiento;
use Illuminate\Support\Facades\DB;

/**
 * Operaciones del médico (CU14 a CU18): agenda, consulta, tratamiento,
 * estudios, historia clínica y bloqueo de agenda.
 */
class AtencionService
{
    public function __construct(private CitaService $citas)
    {
    }

    /** La consulta debe ser de una cita del médico y seguir en atención */
    private function exigirEditable(Consulta $consulta, Medico $medico): void
    {
        if ($consulta->cita->idmedico !== $medico->idmedico) {
            abort(404);
        }
        if ($consulta->cita->codigoestado !== EstadoCita::EN_ATENCION) {
            throw new ReglaNegocio('La consulta ya fue cerrada y no se puede modificar.');
        }
    }

    // ------------------------------------------------------------------
    // CU15 Registrar consulta médica
    // ------------------------------------------------------------------

    /** MEDICO.registrarConsulta() / CONSULTA.registrarSignos() + diagnosticar() */
    public function registrarConsulta(Consulta $consulta, Medico $medico, array $datos): void
    {
        $this->exigirEditable($consulta, $medico);
        DB::transaction(function () use ($consulta, $datos) {
            $consulta->update($datos);
            Bitacora::registrar('REGISTRAR_CONSULTA', 'ATENCION', 'consulta', $consulta->idconsulta, "Registró signos vitales y diagnóstico de la cita {$consulta->cita->codigo}");
        });
    }

    /** CONSULTA.cerrar(): exige diagnóstico y deja la cita ATENDIDA */
    public function cerrar(Consulta $consulta, Medico $medico): void
    {
        $this->exigirEditable($consulta, $medico);
        if (! $consulta->diagnostico) {
            throw new ReglaNegocio('Registre el diagnóstico antes de finalizar la consulta.');
        }
        DB::transaction(function () use ($consulta) {
            $this->citas->finalizar($consulta->cita);
            Bitacora::registrar('CERRAR_CONSULTA', 'ATENCION', 'consulta', $consulta->idconsulta, "Finalizó la consulta de la cita {$consulta->cita->codigo}");
        });
    }

    // ------------------------------------------------------------------
    // CU17 Registrar tratamiento
    // ------------------------------------------------------------------

    /** MEDICO.recetarTratamiento() / TRATAMIENTO.indicar() */
    public function recetar(Consulta $consulta, Medico $medico, array $datos): Tratamiento
    {
        $this->exigirEditable($consulta, $medico);
        $medicamento = Medicamento::findOrFail($datos['idmedicamento']);
        if ($medicamento->estado !== 'ACTIVO') {
            throw new ReglaNegocio('El medicamento seleccionado no está disponible.');
        }
        return DB::transaction(function () use ($consulta, $datos, $medicamento) {
            $tratamiento = $consulta->tratamientos()->create($datos);
            Bitacora::registrar('RECETAR_TRATAMIENTO', 'ATENCION', 'tratamiento', $tratamiento->idtratamiento, "Recetó {$medicamento->nombre} en la cita {$consulta->cita->codigo}");
            return $tratamiento;
        });
    }

    /** TRATAMIENTO.suspender(): lo quita de la receta mientras la consulta está abierta */
    public function suspender(Tratamiento $tratamiento, Medico $medico): void
    {
        $this->exigirEditable($tratamiento->consulta, $medico);
        DB::transaction(function () use ($tratamiento) {
            $tratamiento->delete();
            Bitacora::registrar('SUSPENDER_TRATAMIENTO', 'ATENCION', 'tratamiento', $tratamiento->idtratamiento, "Quitó {$tratamiento->medicamento->nombre} de la receta");
        });
    }

    // ------------------------------------------------------------------
    // CU18 Gestionar estudios médicos
    // ------------------------------------------------------------------

    /** MEDICO.solicitarEstudio() / ESTUDIO.solicitar() */
    public function solicitarEstudio(Consulta $consulta, Medico $medico, array $datos): Estudio
    {
        $this->exigirEditable($consulta, $medico);
        $catalogo = CatalogoEstudio::findOrFail($datos['idcatalogo']);
        if ($catalogo->estado !== 'ACTIVO') {
            throw new ReglaNegocio('El estudio seleccionado no está disponible.');
        }
        return DB::transaction(function () use ($consulta, $datos, $catalogo) {
            $estudio = $consulta->estudios()->create($datos + ['estado' => 'SOLICITADO', 'fechasolicitud' => now()]);
            Bitacora::registrar('SOLICITAR_ESTUDIO', 'ATENCION', 'estudio', $estudio->idestudio, "Solicitó {$catalogo->nombre} en la cita {$consulta->cita->codigo}");
            return $estudio;
        });
    }

    /** ESTUDIO.registrarResultado() + adjuntarArchivo(); se notifica al paciente */
    public function registrarResultado(Estudio $estudio, array $datos): void
    {
        if ($estudio->estado === 'ANULADO') {
            throw new ReglaNegocio('El estudio fue anulado.');
        }
        DB::transaction(function () use ($estudio, $datos) {
            $estudio->update($datos + ['estado' => 'COMPLETADO', 'fecharesultado' => now()]);
            $cita = $estudio->consulta->cita;
            Notificacion::programar('RESULTADO', "Ya está disponible el resultado de su estudio «{$estudio->catalogo->nombre}» (cita {$cita->codigo}).", null, $estudio->idestudio);
            Bitacora::registrar('REGISTRAR_RESULTADO', 'ATENCION', 'estudio', $estudio->idestudio, "Registró el resultado de {$estudio->catalogo->nombre} (cita {$cita->codigo})");
        });
    }

    // ------------------------------------------------------------------
    // CU16 Gestionar historial clínico
    // ------------------------------------------------------------------

    /** HISTORIACLINICA.actualizarAntecedentes() */
    public function actualizarAntecedentes(Paciente $paciente, array $datos): HistoriaClinica
    {
        return DB::transaction(function () use ($paciente, $datos) {
            $historia = HistoriaClinica::firstOrCreate(
                ['idpaciente' => $paciente->idpaciente],
                ['numero' => 'HC-' . str_pad((string) $paciente->idpaciente, 6, '0', STR_PAD_LEFT)],
            );
            $historia->update($datos);
            Bitacora::registrar('ACTUALIZAR_ANTECEDENTES', 'ATENCION', 'historiaclinica', $historia->idhistoria, "Actualizó los antecedentes de la historia {$historia->numero}");
            return $historia;
        });
    }

    // ------------------------------------------------------------------
    // CU14 Agenda: bloquear y liberar
    // ------------------------------------------------------------------

    /** MEDICO.bloquearAgenda() / BLOQUEOAGENDA.crear(): no se permite sobre citas pendientes */
    public function bloquear(Medico $medico, array $datos): BloqueoAgenda
    {
        return DB::transaction(function () use ($medico, $datos) {
            Medico::whereKey($medico->idmedico)->lockForUpdate()->first();
            $conflictos = Cita::with('paciente')->where('idmedico', $medico->idmedico)->whereDate('fecha', $datos['fecha'])
                ->whereIn('codigoestado', EstadoCita::PENDIENTES)
                ->when($datos['horainicio'] ?? null, fn ($q) => $q->where('horainicio', '<', $datos['horafin'])->where('horafin', '>', $datos['horainicio']))
                ->orderBy('horainicio')->get();
            if ($conflictos->isNotEmpty()) {
                throw new ReglaNegocio(
                    "Hay {$conflictos->count()} cita(s) pendiente(s) en ese horario. Reprográmelas o cancélelas antes de bloquear la agenda.",
                    $conflictos->map(fn ($c) => "{$c->hora} — {$c->paciente->nombre_completo} ({$c->codigo})")->all(),
                );
            }
            $bloqueo = $medico->bloqueos()->create($datos);
            Bitacora::registrar('BLOQUEAR_AGENDA', 'AGENDA', 'bloqueoagenda', $bloqueo->idbloqueo,
                "Bloqueó la agenda del {$datos['fecha']}" . (($datos['horainicio'] ?? null) ? " de {$datos['horainicio']} a {$datos['horafin']}" : ' (día completo)'));
            return $bloqueo;
        });
    }

    /** BLOQUEOAGENDA.liberar() */
    public function liberar(BloqueoAgenda $bloqueo): void
    {
        DB::transaction(function () use ($bloqueo) {
            $bloqueo->delete();
            Bitacora::registrar('LIBERAR_AGENDA', 'AGENDA', 'bloqueoagenda', $bloqueo->idbloqueo, "Liberó el bloqueo del {$bloqueo->fecha->format('Y-m-d')}");
        });
    }
}
