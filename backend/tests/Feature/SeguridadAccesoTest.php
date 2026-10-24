<?php

namespace Tests\Feature;

use App\Models\Bitacora;
use App\Models\Rol;
use App\Models\Usuario;
use Tests\TestCase;

/** P1 Seguridad y acceso: CU1 Iniciar sesión, CU3 Gestionar usuarios */
class SeguridadAccesoTest extends TestCase
{
    public function test_inicia_sesion_con_usuario_y_registra_bitacora(): void
    {
        $this->post('/login', ['login' => 'admin', 'password' => 'Admin123*'])->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($this->usuario('admin'));
        $this->assertNotNull($this->usuario('admin')->ultimoacceso);
        $this->assertTrue(Bitacora::where('accion', 'INICIAR_SESION')->where('idregistro', $this->usuario('admin')->idusuario)->exists());
    }

    public function test_paciente_inicia_sesion_con_su_ci(): void
    {
        $this->post('/login', ['login' => '1234567', 'password' => 'Paciente123*'])->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($this->usuario('paciente'));
    }

    public function test_rechaza_credenciales_incorrectas_con_mensaje_claro(): void
    {
        $this->from('/login')->post('/login', ['login' => 'admin', 'password' => 'mala'])
            ->assertRedirect('/login')->assertSessionHasErrors(['login' => 'Usuario o contraseña incorrectos.']);
        $this->assertGuest();
    }

    public function test_cuenta_inactiva_no_puede_ingresar(): void
    {
        $this->usuario('recepcion')->update(['estado' => 'INACTIVO']);
        $this->post('/login', ['login' => 'recepcion', 'password' => 'Recepcion123*'])
            ->assertSessionHasErrors(['login' => 'Su cuenta está inactiva. Comuníquese con la clínica.']);
        $this->assertGuest();
    }

    public function test_control_de_acceso_por_rol(): void
    {
        $this->get('/usuarios')->assertRedirect('/login');
        $this->actingAs($this->usuario('paciente'))->get('/usuarios')->assertForbidden();
        $this->actingAs($this->usuario('recepcion'))->get('/usuarios')->assertForbidden();
        $this->actingAs($this->usuario('recepcion'))->get('/pacientes')->assertOk();
        $this->actingAs($this->usuario('dr.perez'))->get('/pacientes')->assertForbidden();
        $this->actingAs($this->usuario('paciente'))->get('/agenda')->assertForbidden();
        $this->actingAs($this->usuario('admin'))->get('/usuarios')->assertOk()->assertSee('dr.perez');
    }

    public function test_cerrar_sesion(): void
    {
        $this->actingAs($this->usuario('admin'))->post('/logout')->assertRedirect('/login');
        $this->assertGuest();
        $this->assertTrue(Bitacora::where('accion', 'CERRAR_SESION')->exists());
    }

    public function test_crea_usuario_con_validacion_en_espanol(): void
    {
        $admin = $this->usuario('admin');
        $this->actingAs($admin)->post('/usuarios', ['nombre' => 'X', 'username' => 'admin', 'email' => 'no-correo', 'password' => '123'])
            ->assertSessionHasErrors([
                'nombre' => 'Nombre debe tener al menos 3 caracteres.',
                'username' => 'Ya existe un registro con ese nombre de usuario.',
                'email' => 'Correo electrónico debe ser un correo electrónico válido.',
                'idrol' => 'Rol es obligatorio.',
            ]);

        $this->actingAs($admin)->post('/usuarios', [
            'nombre' => 'Luis Recepción', 'username' => 'Luis.R', 'email' => 'luis@clinica.com',
            'idrol' => Rol::where('nombre', 'RECEPCIONISTA')->value('idrol'),
            'password' => 'Clave1234', 'password_confirmation' => 'Clave1234',
        ])->assertRedirect('/usuarios');

        $nuevo = Usuario::where('username', 'luis.r')->first();
        $this->assertNotNull($nuevo);
        $this->assertTrue($nuevo->tieneRol('RECEPCIONISTA'));
        $this->assertStringStartsWith('$2y$', $nuevo->password);
    }

    public function test_desactiva_usuario_pero_no_a_si_mismo(): void
    {
        $admin = $this->usuario('admin');
        $this->actingAs($admin)->patch("/usuarios/{$admin->idusuario}/estado")->assertSessionHas('error');
        $recepcion = $this->usuario('recepcion');
        $this->actingAs($admin)->patch("/usuarios/{$recepcion->idusuario}/estado");
        $this->assertSame('INACTIVO', $recepcion->fresh()->estado);
    }

    public function test_cambia_su_contrasena(): void
    {
        $paciente = $this->usuario('paciente');
        $this->actingAs($paciente)->put('/cuenta/password', ['actual' => 'otra', 'password' => 'Nueva1234', 'password_confirmation' => 'Nueva1234'])
            ->assertSessionHasErrors(['actual' => 'La contraseña actual no es correcta.']);
        $this->actingAs($paciente)->put('/cuenta/password', ['actual' => 'Paciente123*', 'password' => 'Nueva1234', 'password_confirmation' => 'Nueva1234'])
            ->assertSessionHas('ok');
        $this->post('/logout');
        $this->post('/login', ['login' => 'paciente', 'password' => 'Nueva1234'])->assertRedirect(route('dashboard'));
    }

    public function test_roles_del_sistema_no_se_eliminan(): void
    {
        $admin = $this->usuario('admin');
        $medico = Rol::where('nombre', 'MEDICO')->first();
        $this->actingAs($admin)->delete("/roles/{$medico->idrol}")->assertSessionHas('error');
        $this->actingAs($admin)->post('/roles', ['nombre' => 'enfermera', 'descripcion' => 'Apoyo'])->assertSessionHas('ok');
        $this->assertTrue(Rol::where('nombre', 'ENFERMERA')->exists());
    }
}
