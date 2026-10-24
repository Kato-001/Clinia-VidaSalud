<?php

namespace Tests\Feature;

use App\Models\Especialidad;
use App\Models\Medico;
use App\Models\Paciente;
use App\Models\Usuario;
use Tests\TestCase;

/** P2 Gestión médica (CU4–CU6) y P3 Gestión de pacientes (CU7–CU8) */
class GestionMedicaPacientesTest extends TestCase
{
    public function test_crea_y_desactiva_especialidad(): void
    {
        $admin = $this->usuario('admin');
        $this->actingAs($admin)->post('/especialidades', ['nombre' => 'Cardiología'])
            ->assertSessionHasErrors(['nombre' => 'Ya existe un registro con ese nombre.']);
        $this->actingAs($admin)->post('/especialidades', ['nombre' => 'Neurología', 'descripcion' => 'Sistema nervioso'])->assertRedirect('/especialidades');

        $neuro = Especialidad::where('nombre', 'Neurología')->firstOrFail();
        $this->actingAs($admin)->patch("/especialidades/{$neuro->idespecialidad}/estado");
        $this->assertSame('INACTIVO', $neuro->fresh()->estado);
    }

    public function test_registra_medico_con_cuenta_y_especialidades(): void
    {
        $admin = $this->usuario('admin');
        $ids = Especialidad::whereIn('nombre', ['Cardiología', 'Pediatría'])->pluck('idespecialidad')->all();

        $this->actingAs($admin)->post('/medicos', [
            'codigo' => 'MED-0003', 'nombres' => 'Luis', 'apellidos' => 'Vargas', 'registroprofesional' => 'RP-30001',
            'telefono' => '70000003', 'email' => 'lvargas@clinica.com', 'especialidades' => $ids,
            'username' => 'dr.vargas', 'password' => 'Medico1234', 'password_confirmation' => 'Medico1234',
        ])->assertRedirect();

        $medico = Medico::where('codigo', 'MED-0003')->firstOrFail();
        $this->assertCount(2, $medico->especialidades);
        $this->assertTrue($medico->usuario->tieneRol('MEDICO'));

        $this->post('/logout');
        $this->post('/login', ['login' => 'dr.vargas', 'password' => 'Medico1234']);
        $this->get('/panel')->assertRedirect(route('agenda.index'));
    }

    public function test_no_permite_horarios_cruzados(): void
    {
        $admin = $this->usuario('admin');
        $perez = Medico::where('codigo', 'MED-0001')->first();
        $this->actingAs($admin)->post("/medicos/{$perez->idmedico}/horarios", ['diasemana' => 1, 'horainicio' => '11:00', 'horafin' => '13:00'])
            ->assertSessionHas('error', 'Ese bloque se cruza con otro horario del mismo día.');
        $this->actingAs($admin)->post("/medicos/{$perez->idmedico}/horarios", ['diasemana' => 6, 'horainicio' => '10:00', 'horafin' => '09:00'])
            ->assertSessionHasErrors('horafin');
        $this->actingAs($admin)->post("/medicos/{$perez->idmedico}/horarios", ['diasemana' => 6, 'horainicio' => '08:00', 'horafin' => '12:00'])
            ->assertSessionHas('ok');
    }

    public function test_paciente_se_registra_y_queda_con_historia_clinica(): void
    {
        $this->post('/registro', [
            'ci' => '7654321', 'nombres' => 'Pedro', 'apellidos' => 'Gómez', 'fechanacimiento' => '1985-02-20', 'sexo' => 'M',
            'telefono' => '73333333', 'email' => 'pedro@correo.com', 'password' => 'Clave1234', 'password_confirmation' => 'Clave1234',
            'acepta' => '1', 'aceptanotificaciones' => '1',
        ])->assertRedirect(route('dashboard'));

        $paciente = Paciente::where('ci', '7654321')->firstOrFail();
        $this->assertAuthenticatedAs($paciente->usuario);
        $this->assertMatchesRegularExpression('/^PAC-\d{6}$/', $paciente->codigo);
        $this->assertMatchesRegularExpression('/^HC-\d{6}$/', $paciente->historiaClinica->numero);
        $this->assertSame('7654321', $paciente->usuario->username);
        $this->assertTrue($paciente->usuario->tieneRol('PACIENTE'));
    }

    public function test_registro_valida_ci_repetido_y_aceptacion(): void
    {
        $this->post('/registro', [
            'ci' => '1234567', 'nombres' => 'Otra', 'apellidos' => 'Persona', 'fechanacimiento' => '1990-01-01', 'sexo' => 'F',
            'telefono' => '74444444', 'email' => 'otra@correo.com', 'password' => 'Clave1234', 'password_confirmation' => 'Clave1234',
        ])->assertSessionHasErrors(['ci' => 'Ya existe un registro con ese CI.', 'acepta']);
    }

    public function test_recepcion_registra_paciente_con_contacto_y_alergias(): void
    {
        $this->actingAs($this->usuario('recepcion'))->post('/pacientes', [
            'ci' => '9988776', 'nombres' => 'Ana', 'apellidos' => 'Flores', 'fechanacimiento' => '2000-07-01', 'sexo' => 'F',
            'telefono' => '75555555', 'email' => 'ana.flores@correo.com', 'alergias' => 'Ibuprofeno',
            'contacto_nombre' => 'Rosa Flores', 'contacto_parentesco' => 'Madre', 'contacto_telefono' => '76666666',
        ])->assertRedirect();

        $paciente = Paciente::where('ci', '9988776')->firstOrFail();
        $this->assertSame('Ibuprofeno', $paciente->historiaClinica->alergias);
        $this->assertSame('Rosa Flores', $paciente->contactos->first()->nombre);
        // Contraseña inicial = CI
        $this->post('/logout');
        $this->post('/login', ['login' => '9988776', 'password' => '9988776'])->assertRedirect(route('dashboard'));
    }

    public function test_paciente_actualiza_sus_datos_y_contactos(): void
    {
        $usuario = $this->usuario('paciente');
        $p = $usuario->paciente;
        $this->actingAs($usuario)->put('/mi-perfil', [
            'nombres' => 'María José', 'apellidos' => $p->apellidos, 'fechanacimiento' => '1990-05-14', 'sexo' => 'F',
            'telefono' => '71111112', 'email' => 'maria.jose@correo.com',
        ])->assertSessionHas('ok');
        $this->assertSame('María José López', Usuario::find($usuario->idusuario)->nombre);
        $this->assertSame('maria.jose@correo.com', Usuario::find($usuario->idusuario)->email);

        $this->actingAs($usuario)->post('/mi-perfil/contactos', ['nombre' => 'Luis López', 'parentesco' => 'Padre', 'telefono' => '77777777'])->assertSessionHas('ok');
        $this->assertCount(2, $p->fresh()->contactos);
    }
}
