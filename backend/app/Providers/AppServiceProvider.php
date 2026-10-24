<?php

namespace App\Providers;

use App\Models\Notificacion;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Carbon::setLocale('es');

        // En producción todas las URL generadas usan https
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        // Cantidad de notificaciones sin leer del paciente para el menú lateral
        View::composer('compartido.layouts.app', function ($vista) {
            $paciente = auth()->user()?->paciente;
            $vista->with('noLeidas', $paciente ? Notificacion::dePaciente($paciente->idpaciente)->whereNull('leidaen')->count() : 0);
        });
    }
}
