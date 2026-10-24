<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
 * Crea las 21 tablas del diagrama (database/sql/schema.sql) y carga los datos
 * iniciales (database/sql/seed.sql). No usa migraciones de Laravel para no
 * crear tablas que no están en el diagrama.
 */
Artisan::command('mediturno:instalar {--sin-datos : Solo crea las tablas}', function () {
    if (Schema::hasTable('rol')) {
        $this->error('La base de datos ya tiene las tablas de MediTurno. No se hizo ningún cambio.');
        return 1;
    }

    DB::unprepared(file_get_contents(database_path('sql/schema.sql')));
    $this->info('Tablas creadas.');

    if (! $this->option('sin-datos')) {
        DB::unprepared(file_get_contents(database_path('sql/seed.sql')));
        $this->info('Datos iniciales cargados (usuarios de prueba: admin, dr.perez, dra.rojas, paciente).');
    }
    return 0;
})->purpose('Crea las tablas de MediTurno y carga los datos iniciales');
