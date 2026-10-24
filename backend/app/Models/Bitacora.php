<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** BITACORA (diagrama UML) */
class Bitacora extends Model
{
    protected $table = 'bitacora';
    protected $primaryKey = 'idbitacora';
    public $timestamps = false;

    protected $fillable = ['accion', 'modulo', 'tabla', 'idregistro', 'descripcion', 'ip', 'fecha', 'idusuario'];

    protected function casts(): array
    {
        return [
            'fecha' => 'date:Y-m-d',
            'idregistro' => 'integer',
        ];
    }

    /** BITACORA N — 1 USUARIO (REGISTRA) */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'idusuario', 'idusuario');
    }

    /** BITACORA.registrar(): guarda una acción importante del sistema */
    public static function registrar(string $accion, string $modulo, ?string $tabla = null, ?int $idregistro = null, ?string $descripcion = null, ?int $idusuario = null): self
    {
        return self::create([
            'accion' => $accion,
            'modulo' => $modulo,
            'tabla' => $tabla,
            'idregistro' => $idregistro,
            'descripcion' => $descripcion,
            'ip' => request()?->ip(),
            'fecha' => now()->toDateString(),
            'idusuario' => $idusuario ?? auth()->id(),
        ]);
    }
}
