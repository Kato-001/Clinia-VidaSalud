<?php

use App\Exceptions\ReglaNegocio;
use App\Http\Middleware\VerificarRol;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias(['rol' => VerificarRol::class]);
        // En Render la aplicación está detrás de un proxy HTTPS: se confía en él para la IP real y el esquema
        $middleware->trustProxies(at: '*');
        $middleware->redirectGuestsTo(fn () => route('login'));
        $middleware->redirectUsersTo(fn () => route('dashboard'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Reglas de negocio: volver al formulario con el mensaje
        $exceptions->render(function (ReglaNegocio $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['mensaje' => $e->getMessage(), 'detalles' => $e->detalles], 409);
            }
            return back()->withInput()->with('error', $e->getMessage())->with('detalles', $e->detalles);
        });

        // Restricciones de PostgreSQL con mensajes claros
        $exceptions->render(function (QueryException $e, Request $request) {
            $mensaje = match ($e->getCode()) {
                '23505' => 'Ya existe un registro con ese valor'
                    . (preg_match('/Key \(([^)]+)\)=/', $e->getMessage(), $m) ? " ({$m[1]})." : '.'),
                '23503' => str_contains($e->getMessage(), 'is still referenced')
                    ? 'No se puede eliminar: el registro está relacionado con otros datos. Puede desactivarlo.'
                    : 'Alguno de los datos relacionados no existe.',
                '23514' => 'Los datos no cumplen las reglas (revise que la hora de fin sea mayor que la de inicio).',
                default => null,
            };
            if ($mensaje === null) {
                return null;
            }
            return $request->expectsJson()
                ? response()->json(['mensaje' => $mensaje], 409)
                : back()->withInput()->with('error', $mensaje);
        });
    })->create();
