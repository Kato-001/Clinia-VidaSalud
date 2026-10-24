<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** CONFIGURACION (diagrama UML) */
class Configuracion extends Model
{
    protected $table = 'configuracion';
    protected $primaryKey = 'clave';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = ['clave', 'valor', 'descripcion'];

    /** CONFIGURACION.obtener(clave) */
    public static function obtener(string $clave, string $porDefecto = ''): string
    {
        return self::find($clave)?->valor ?? $porDefecto;
    }

    /** Valor numérico positivo de configuración */
    public static function numero(string $clave, int $porDefecto): int
    {
        $valor = (int) self::obtener($clave, (string) $porDefecto);
        return $valor > 0 ? $valor : $porDefecto;
    }
}
