<?php

namespace Tests\Feature;

use App\Models\Bitacora;
use App\Models\Cita;
use App\Models\Especialidad;
use App\Models\HistorialCita;
use App\Models\Medico;
use App\Models\Notificacion;
use App\Models\Paciente;
use App\Models\Usuario;
use Tests\TestCase;

/** P4 Gestión de citas: CU9 a CU14 y sus reglas de negocio */
class GestionCitasTest extends TestCase
{
    private Medico $perez;
    private int $cardiologia;
    private string $dia;

    protected function setUp(): void
    {
        parent::setUp();
        $this->perez = Medico::where('codigo', 'MED-0001')->firstOrFail();
        $this->cardiologia = Especialidad::where('nombre', 'Cardiología')->value('idespecialidad');
        $this->dia = $this->proximoDiaHabil();
    }

    private function reservar(Usuario $quien, string $hora, array $extra = [])
    {
        return $this->actingAs($quien)->post('/citas', $extra + [
            'idmedico' => $this->perez->idmedico, 'idespecialidad' => $this->cardiologia,
            'fecha' => $this->dia, 'horainicio' => $hora, 'modalidad' => 'PRESENCIAL', 'motivo' => 'Control',
        ]);
    }

    private function otroPaciente(): Usuario
    {
        $this->actingAs($this->usuario('recepcion'))->post('/pacientes', [
            'ci' => '5544332', 'nombres' => 'Juan', 'apellidos' => 'Mendoza', 'fechanacimiento' => '1975-03-03', 'sexo' => 'M',
            'telefono' => '78888888', 'email' => 'juan.m@correo.com',
        ]);
        return Paciente::where('ci', '5544332')->firstOrFail()->usuario;
    }

    public function test_portal_publico_y_disponibilidad(): void
    {
        $this->get('/')->assertOk()->assertSee('Juan Pérez')->assertSee('Cardiología');
        $this->get('/horarios')->assertOk();
        $this->getJson("/api/especialidades/{$this->cardiologia}/medicos")
            ->assertOk()->assertJsonFragment(['id' => $this->perez->idmedico, 'nombre' => 'Dr(a). Juan Pérez']);

        $r = $this->getJson("/api/medicos/{$this->perez->idmedico}/bloques?fecha={$this->dia}")->assertOk();
        $this->assertCount(32, $r->json('bloques')); // 8 horas en fichas de 15 minutos
        $this->assertSame(['inicio' => '08:00', 'fin' => '08:15', 'disponible' => true], $r->json('bloques.0'));
    }

    public function test_reserva_registra_historial_notificacion_y_bitacora(): void
    {
        $this->reservar($this->usuario('paciente'), '09:00')->assertRedirect()->assertSessionHas('ok');

        $cita = Cita::where('idmedico', $this->perez->idmedico)->whereDate('fecha', $this->dia)->firstOrFail();
        $this->assertMatchesRegularExpression('/^CIT-\d{6}$/', $cita->codigo);
        $this->assertSame('PROGRAMADA', $cita->codigoestado);
        $this->assertSame('09:15:00', $cita->horafin);
        $this->assertSame(['RESERVA'], HistorialCita::where('idcita', $cita->idcita)->pluck('accion')->all());
        $this->assertSame(['RESERVA'], Notificacion::where('idcita', $cita->idcita)->pluck('tipo')->all());
        $this->assertTrue(Bitacora::where('accion', 'RESERVAR_CITA')->where('idregistro', $cita->idcita)->exists());

        $bloques = $this->getJson("/api/medicos/{$this->perez->idmedico}/bloques?fecha={$this->dia}")->json('bloques');
        $this->assertFalse(collect($bloques)->firstWhere('inicio', '09:00')['disponible']);
    }

    public function test_no_permite_dos_citas_del_medico_en_el_mismo_horario(): void
    {
        $this->reservar($this->usuario('paciente'), '09:00');
        $this->reservar($this->otroPaciente(), '09:00')
            ->assertSessionHas('error', 'El médico ya tiene una cita en ese horario. Elija otra ficha.');
        $this->assertSame(1, Cita::where('idmedico', $this->perez->idmedico)->whereDate('fecha', $this->dia)->count());
    }

    public function test_respeta_horario_especialidad_y_fecha(): void
    {
        $paciente = $this->usuario('paciente');
        $this->reservar($paciente, '12:30')->assertSessionHas('error', 'La hora elegida está fuera del horario de atención del médico.');
        $this->reservar($paciente, '10:00', ['idespecialidad' => Especialidad::where('nombre', 'Pediatría')->value('idespecialidad')])
            ->assertSessionHas('error', 'El médico no atiende la especialidad seleccionada.');
        $this->reservar($paciente, '10:00', ['fecha' => now()->subDay()->toDateString()])->assertSessionHasErrors('fecha');
    }

    public function test_respeta_bloqueo_de_agenda_y_no_bloquea_sobre_citas(): void
    {
        $medico = $this->usuario('dr.perez');
        $this->reservar($this->usuario('paciente'), '09:00');

        $this->actingAs($medico)->post('/agenda/bloqueos', ['fecha' => $this->dia, 'horainicio' => '09:00', 'horafin' => '10:00', 'tipo' => 'PERMISO'])
            ->assertSessionHas('error');
        $this->actingAs($medico)->post('/agenda/bloqueos', ['fecha' => $this->dia, 'horainicio' => '10:00', 'horafin' => '11:00', 'tipo' => 'CAPACITACION'])
            ->assertSessionHas('ok');

        $this->reservar($this->otroPaciente(), '10:30')->assertSessionHas('error', 'El médico no atiende en ese horario (agenda bloqueada por capacitación).');
    }

    public function test_confirma_reprograma_y_cancela_guardando_historial(): void
    {
        $paciente = $this->usuario('paciente');
        $this->reservar($paciente, '09:00');
        $cita = Cita::where('idmedico', $this->perez->idmedico)->whereDate('fecha', $this->dia)->firstOrFail();

        $this->actingAs($paciente)->post("/citas/{$cita->idcita}/confirmar")->assertSessionHas('ok');
        $this->assertSame('CONFIRMADA', $cita->fresh()->codigoestado);

        $this->actingAs($paciente)->put("/citas/{$cita->idcita}/reprogramar", ['fecha' => $this->dia, 'horainicio' => '11:00', 'motivo' => 'Trabajo'])->assertRedirect();
        $cita->refresh();
        $this->assertSame(['REPROGRAMADA', '11:00:00'], [$cita->codigoestado, $cita->horainicio]);
        $cambio = HistorialCita::where('idcita', $cita->idcita)->where('accion', 'REPROGRAMACION')->firstOrFail();
        $this->assertSame(['09:00:00', '11:00:00'], [$cambio->horaanterior, $cambio->horanueva]);

        // El horario anterior queda libre
        $this->reservar($this->otroPaciente(), '09:00')->assertSessionHas('ok');

        $this->actingAs($paciente)->post("/citas/{$cita->idcita}/cancelar", ['motivo' => 'Viaje'])->assertSessionHas('ok');
        $this->assertSame('CANCELADA', $cita->fresh()->codigoestado);
        $this->actingAs($paciente)->post("/citas/{$cita->idcita}/cancelar", ['motivo' => 'Otra vez'])->assertSessionHas('error');

        $this->assertSame(['RESERVA', 'CONFIRMACION', 'REPROGRAMACION', 'CANCELACION'], HistorialCita::where('idcita', $cita->idcita)->orderBy('idhistorial')->pluck('accion')->all());
        $this->assertSame(['RESERVA', 'REPROGRAMACION', 'CANCELACION'], Notificacion::where('idcita', $cita->idcita)->orderBy('idnotificacion')->pluck('tipo')->all());
    }

    public function test_paciente_no_ve_citas_ajenas(): void
    {
        $this->reservar($this->usuario('paciente'), '09:00');
        $cita = Cita::where('idmedico', $this->perez->idmedico)->whereDate('fecha', $this->dia)->firstOrFail();
        $otro = $this->otroPaciente();

        $this->actingAs($otro)->get("/citas/{$cita->idcita}")->assertNotFound();
        $this->actingAs($otro)->post("/citas/{$cita->idcita}/cancelar", ['motivo' => 'x x x'])->assertNotFound();
        $this->actingAs($this->usuario('dra.rojas'))->get("/citas/{$cita->idcita}")->assertNotFound();
        $this->actingAs($this->usuario('recepcion'))->get("/citas/{$cita->idcita}")->assertOk();
    }

    public function test_recepcion_programa_cita_para_un_paciente(): void
    {
        $paciente = Paciente::where('ci', '1234567')->first();
        $this->reservar($this->usuario('recepcion'), '15:00', ['idpaciente' => $paciente->idpaciente])->assertSessionHas('ok');
        $this->assertTrue(Cita::where('idpaciente', $paciente->idpaciente)->where('horainicio', '15:00')->exists());
        $this->actingAs($this->usuario('recepcion'))->getJson('/api/pacientes?q=Lóp')->assertJsonFragment(['ci' => '1234567']);
    }

    public function test_anticipacion_minima_para_cancelar(): void
    {
        $paciente = $this->usuario('paciente');
        $this->reservar($paciente, '09:00');
        $cita = Cita::where('idmedico', $this->perez->idmedico)->whereDate('fecha', $this->dia)->firstOrFail();
        // La cita pasa a dentro de 1 hora
        $cita->update(['fecha' => now()->toDateString(), 'horainicio' => now()->addHour()->format('H:i'), 'horafin' => now()->addHour()->addMinutes(15)->format('H:i')]);

        $this->actingAs($paciente)->post("/citas/{$cita->idcita}/cancelar", ['motivo' => 'Tarde'])
            ->assertSessionHas('error', 'Solo puede cancelar la cita con al menos 2 hora(s) de anticipación. Comuníquese con la clínica.');
        $this->actingAs($this->usuario('recepcion'))->post("/citas/{$cita->idcita}/cancelar", ['motivo' => 'Llamó por teléfono'])->assertSessionHas('ok');
    }
}
