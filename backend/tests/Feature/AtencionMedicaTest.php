<?php

namespace Tests\Feature;

use App\Models\CatalogoEstudio;
use App\Models\Cita;
use App\Models\Especialidad;
use App\Models\Estudio;
use App\Models\Medicamento;
use App\Models\Medico;
use App\Models\Notificacion;
use Tests\TestCase;

/** P5 Atención médica: CU13, CU15 a CU19 */
class AtencionMedicaTest extends TestCase
{
    private Cita $cita;

    protected function setUp(): void
    {
        parent::setUp();
        $dia = $this->proximoDiaHabil();
        $this->actingAs($this->usuario('paciente'))->post('/citas', [
            'idmedico' => Medico::where('codigo', 'MED-0001')->value('idmedico'),
            'idespecialidad' => Especialidad::where('nombre', 'Cardiología')->value('idespecialidad'),
            'fecha' => $dia, 'horainicio' => '15:00', 'modalidad' => 'PRESENCIAL', 'motivo' => 'Dolor de pecho',
        ]);
        $this->cita = Cita::whereDate('fecha', $dia)->where('horainicio', '15:00')->firstOrFail();
        // Simula que llegó el día de la cita
        $this->cita->update(['fecha' => today()->toDateString()]);
    }

    public function test_flujo_completo_de_atencion(): void
    {
        $medico = $this->usuario('dr.perez');

        // CU14 agenda y CU13 asistencia
        $this->actingAs($medico)->get('/agenda')->assertOk()->assertSee('María López');
        $this->actingAs($medico)->get('/asistencia')->assertOk()->assertSee('Asistió');
        $this->actingAs($medico)->post("/citas/{$this->cita->idcita}/atender")->assertRedirect();
        $consulta = $this->cita->fresh()->consulta;
        $this->assertSame('EN_ATENCION', $this->cita->fresh()->codigoestado);
        $this->assertSame('Dolor de pecho', $consulta->motivo);

        // CU15 consulta con validación
        $this->actingAs($medico)->get("/consultas/{$consulta->idconsulta}")->assertOk()->assertSee('Penicilina');
        $this->actingAs($medico)->put("/consultas/{$consulta->idconsulta}", ['temperatura' => 60, 'presionarterial' => '12-8'])
            ->assertSessionHasErrors(['temperatura' => 'Temperatura debe estar entre 30 y 45.', 'presionarterial' => 'La presión arterial debe tener el formato 120/80.']);
        $this->actingAs($medico)->put("/consultas/{$consulta->idconsulta}", ['peso' => 70.5, 'talla' => 168, 'presionarterial' => '120/80', 'temperatura' => 36.7, 'frecuenciacardiaca' => 72])
            ->assertSessionHas('ok');
        $this->assertSame(70.5, $consulta->fresh()->peso);

        // CU17 receta y CU18 estudio
        $this->actingAs($medico)->post("/consultas/{$consulta->idconsulta}/tratamientos", [
            'idmedicamento' => Medicamento::where('nombre', 'like', 'Paracetamol%')->value('idmedicamento'),
            'dosis' => '500 mg', 'frecuencia' => 'Cada 8 horas', 'duracion' => '5 días',
        ])->assertSessionHas('ok');
        $this->actingAs($medico)->post("/consultas/{$consulta->idconsulta}/estudios", [
            'idcatalogo' => CatalogoEstudio::where('nombre', 'Electrocardiograma')->value('idcatalogo'), 'indicaciones' => 'En reposo',
        ])->assertSessionHas('ok');

        // No se cierra sin diagnóstico
        $this->actingAs($medico)->post("/consultas/{$consulta->idconsulta}/cerrar")->assertSessionHas('error', 'Registre el diagnóstico antes de finalizar la consulta.');
        $this->actingAs($medico)->put("/consultas/{$consulta->idconsulta}", ['diagnostico' => 'Dolor torácico atípico']);
        $this->actingAs($medico)->post("/consultas/{$consulta->idconsulta}/cerrar")->assertRedirect();
        $this->assertSame('ATENDIDA', $this->cita->fresh()->codigoestado);
        $this->actingAs($medico)->put("/consultas/{$consulta->idconsulta}", ['observaciones' => 'x'])->assertSessionHas('error', 'La consulta ya fue cerrada y no se puede modificar.');

        // CU18 resultado y notificación al paciente
        $estudio = Estudio::where('idconsulta', $consulta->idconsulta)->firstOrFail();
        $this->actingAs($medico)->get('/estudios')->assertOk()->assertSee('Electrocardiograma');
        $this->actingAs($medico)->put("/estudios/{$estudio->idestudio}/resultado", ['resultado' => 'Ritmo sinusal normal', 'archivoresultado' => 'https://archivos.clinica.com/ecg.pdf'])
            ->assertRedirect('/estudios');
        $this->assertSame('COMPLETADO', $estudio->fresh()->estado);
        $this->assertTrue(Notificacion::where('idestudio', $estudio->idestudio)->where('tipo', 'RESULTADO')->exists());

        // CU19 el paciente ve historial, resultados y notificaciones
        $paciente = $this->usuario('paciente');
        $this->actingAs($paciente)->get('/mi-historial')->assertOk()->assertSee('Dolor torácico atípico')->assertSee('Paracetamol 500 mg');
        $this->actingAs($paciente)->get('/mis-resultados')->assertOk()->assertSee('Ritmo sinusal normal');
        $this->actingAs($paciente)->get('/notificaciones')->assertOk()->assertSee('Electrocardiograma');
        $this->actingAs($paciente)->post('/notificaciones/leidas')->assertSessionHas('ok');
        $this->assertSame(0, Notificacion::dePaciente($paciente->paciente->idpaciente)->whereNull('leidaen')->count());
    }

    public function test_marcar_no_asistio(): void
    {
        $this->actingAs($this->usuario('recepcion'))->post("/citas/{$this->cita->idcita}/no-asistio")->assertSessionHas('ok');
        $this->assertSame('NO_ASISTIO', $this->cita->fresh()->codigoestado);
    }

    public function test_otro_medico_no_atiende_la_cita_ni_ve_la_historia(): void
    {
        $rojas = $this->usuario('dra.rojas');
        $this->actingAs($rojas)->post("/citas/{$this->cita->idcita}/atender")->assertNotFound();
        $this->actingAs($rojas)->get("/pacientes/{$this->cita->idpaciente}/historia")->assertForbidden();
    }

    public function test_medico_actualiza_antecedentes(): void
    {
        $this->actingAs($this->usuario('dr.perez'))->put("/pacientes/{$this->cita->idpaciente}/historia", ['enfermedadescronicas' => 'Hipertensión'])
            ->assertSessionHas('ok');
        $this->assertSame('Hipertensión', $this->cita->paciente->historiaClinica->fresh()->enfermedadescronicas);
    }
}
