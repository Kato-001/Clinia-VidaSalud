<?php

namespace Tests\Feature;

use App\Models\Medico;
use App\Models\Paciente;
use Tests\TestCase;

/** Todas las pantallas abren sin errores para el rol que corresponde (incluye CU20 reportes) */
class PantallasTest extends TestCase
{
    public function test_pantallas_publicas(): void
    {
        foreach (['/', '/horarios', '/login', '/registro'] as $ruta) {
            $this->get($ruta)->assertOk();
        }
        $this->assertStringStartsWith('text/css', $this->get('/assets/css/compartido/mediturno.css')->assertOk()->headers->get('Content-Type'));
        $this->get('/assets/../.env')->assertNotFound();
    }

    public function test_pantallas_del_administrador(): void
    {
        $admin = $this->usuario('admin');
        $medico = Medico::first();
        $paciente = Paciente::first();
        $rutas = [
            '/panel', '/usuarios', '/usuarios/create', "/usuarios/{$admin->idusuario}/edit", '/roles', '/configuracion',
            '/especialidades', '/especialidades/create', '/medicos', '/medicos/create', "/medicos/{$medico->idmedico}",
            "/medicos/{$medico->idmedico}/edit", "/medicos/{$medico->idmedico}/horarios", '/medicamentos', '/catalogo-estudios',
            '/pacientes', '/pacientes/create', "/pacientes/{$paciente->idpaciente}", "/pacientes/{$paciente->idpaciente}/edit",
            '/citas', '/citas/nueva', '/asistencia', '/agenda', '/reportes', '/bitacora', '/cuenta/password',
        ];
        foreach ($rutas as $ruta) {
            $this->actingAs($admin)->get($ruta)->assertOk();
        }
        $this->actingAs($admin)->get('/reportes/exportar')->assertOk()->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    public function test_pantallas_de_recepcion_medico_y_paciente(): void
    {
        foreach (['/panel', '/citas', '/citas/nueva', '/asistencia', '/agenda', '/pacientes', '/medicos'] as $ruta) {
            $this->actingAs($this->usuario('recepcion'))->get($ruta)->assertOk();
        }
        $this->actingAs($this->usuario('dr.perez'))->get('/panel')->assertRedirect(route('agenda.index'));
        foreach (['/agenda', '/asistencia', '/citas', '/estudios', '/agenda/bloqueos'] as $ruta) {
            $this->actingAs($this->usuario('dr.perez'))->get($ruta)->assertOk();
        }
        foreach (['/panel', '/citas', '/citas/nueva', '/mi-historial', '/mis-resultados', '/notificaciones', '/mi-perfil'] as $ruta) {
            $this->actingAs($this->usuario('paciente'))->get($ruta)->assertOk();
        }
    }
}
