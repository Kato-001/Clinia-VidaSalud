<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Control de acceso por ROL. Uso en rutas: ->middleware('rol:ADMINISTRADOR,RECEPCIONISTA')
 */
class VerificarRol
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $usuario = $request->user();

        if (! $usuario) {
            return redirect()->route('login');
        }
        if ($usuario->estado !== 'ACTIVO') {
            auth()->logout();
            return redirect()->route('login')->withErrors(['login' => 'Su cuenta está inactiva. Comuníquese con la clínica.']);
        }
        abort_unless($usuario->tieneRol(...$roles), 403);

        return $next($request);
    }
}
