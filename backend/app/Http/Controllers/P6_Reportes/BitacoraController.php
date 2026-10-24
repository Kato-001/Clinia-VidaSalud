<?php

namespace App\Http\Controllers\P6_Reportes;

use App\Http\Controllers\Controller;
use App\Models\Bitacora;
use Illuminate\Http\Request;

/** Bitácora del sistema (solo lectura) */
class BitacoraController extends Controller
{
    public function index(Request $request)
    {
        $registros = Bitacora::with('usuario')
            ->when($request->q, fn ($q, $t) => $q->where(fn ($w) => $w->where('descripcion', 'ilike', "%{$t}%")->orWhere('accion', 'ilike', "%{$t}%")
                ->orWhereHas('usuario', fn ($u) => $u->where('username', 'ilike', "%{$t}%"))))
            ->when($request->modulo, fn ($q, $m) => $q->where('modulo', $m))
            ->when($request->desde, fn ($q, $d) => $q->whereDate('fecha', '>=', $d))
            ->when($request->hasta, fn ($q, $h) => $q->whereDate('fecha', '<=', $h))
            ->orderByDesc('idbitacora')->paginate(25)->withQueryString();

        return view('P6_Reportes.CU20_GenerarReportes.bitacora', [
            'registros' => $registros,
            'modulos' => Bitacora::query()->distinct()->orderBy('modulo')->pluck('modulo'),
        ]);
    }
}
