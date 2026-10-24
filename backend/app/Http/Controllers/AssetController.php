<?php

namespace App\Http\Controllers;

use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Sirve los archivos de frontend/assets (CSS, JS, imágenes) en /assets/...
 * Así las vistas quedan en /frontend sin copiar archivos a /backend/public.
 */
class AssetController extends Controller
{
    private const TIPOS = [
        'css' => 'text/css',
        'js' => 'application/javascript',
        'svg' => 'image/svg+xml',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'ico' => 'image/x-icon',
        'woff2' => 'font/woff2',
    ];

    public function __invoke(string $ruta): BinaryFileResponse
    {
        $base = realpath(base_path('../frontend/assets'));
        $archivo = realpath($base . DIRECTORY_SEPARATOR . $ruta);
        $extension = strtolower(pathinfo((string) $archivo, PATHINFO_EXTENSION));

        // Evita salir de la carpeta assets (../) y tipos no permitidos
        abort_unless($archivo && str_starts_with($archivo, $base) && is_file($archivo) && isset(self::TIPOS[$extension]), 404);

        return response()->file($archivo, [
            'Content-Type' => self::TIPOS[$extension],
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}
