<?php

namespace Tests;

use App\Models\Usuario;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Carbon;

/**
 * Las pruebas usan una base PostgreSQL con schema.sql + seed.sql.
 * Cada prueba se ejecuta en una transacción que se revierte al terminar.
 */
abstract class TestCase extends BaseTestCase
{
    use DatabaseTransactions;

    /** Usuario de prueba del seed: admin, recepcion, dr.perez, dra.rojas, paciente */
    protected function usuario(string $username): Usuario
    {
        return Usuario::where('username', $username)->firstOrFail();
    }

    /** Próximo día de lunes a viernes a partir de mañana (Dr. Pérez atiende 08–12 y 14–18) */
    protected function proximoDiaHabil(): string
    {
        $dia = Carbon::tomorrow();
        while ($dia->isWeekend()) {
            $dia->addDay();
        }
        return $dia->toDateString();
    }
}
