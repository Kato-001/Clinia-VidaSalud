<?php

namespace App\Exceptions;

use Exception;

/**
 * Regla de negocio incumplida (horario ocupado, cita ya cancelada, etc.).
 * Se muestra al usuario como mensaje de error y se conserva lo que escribió.
 */
class ReglaNegocio extends Exception
{
    public function __construct(string $mensaje, public array $detalles = [])
    {
        parent::__construct($mensaje);
    }
}
