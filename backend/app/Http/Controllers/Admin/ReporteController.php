<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Boleto;
use App\Models\EventoAbordaje;
use App\Models\Ruta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReporteController extends Controller
{
    public function ventasDiarias(Request $request)
    {
        $fecha = $request->date('fecha') ?: now();

        $ventas = Boleto::query()
            ->select('vendedor_id', DB::raw('COUNT(*) as boletos'), DB::raw('SUM(precio_final) as ingresos'))
            ->with('vendedor:id,nombre,email,rol')
            ->whereDate('fecha_emision', $fecha)
            ->whereIn('estado', ['pagado', 'abordado'])
            ->groupBy('vendedor_id')
            ->get();

        return $this->success($ventas, 'Ventas diarias por vendedor.');
    }

    public function ocupacionPorRuta(Request $request)
    {
        $desde = $request->date('desde') ?: now()->subMonth();
        $hasta = $request->date('hasta') ?: now();

        $data = Ruta::query()
            ->select('rutas.id', 'rutas.codigo', 'rutas.origen', 'rutas.destino')
            ->selectRaw('COUNT(DISTINCT viajes.id) as viajes')
            ->selectRaw('COUNT(boletos.id) as boletos_emitidos')
            ->selectRaw('SUM(buses.capacidad) as capacidad_programada')
            ->leftJoin('viajes', 'viajes.ruta_id', '=', 'rutas.id')
            ->leftJoin('buses', 'buses.id', '=', 'viajes.bus_id')
            ->leftJoin('boletos', function ($join): void {
                $join->on('boletos.viaje_id', '=', 'viajes.id')->whereIn('boletos.estado', ['pagado', 'abordado']);
            })
            ->whereBetween('viajes.fecha_salida', [$desde->startOfDay(), $hasta->endOfDay()])
            ->groupBy('rutas.id', 'rutas.codigo', 'rutas.origen', 'rutas.destino')
            ->get()
            ->map(function ($row): array {
                $capacidad = max((int) $row->capacidad_programada, 1);

                return [
                    'ruta' => "{$row->origen} - {$row->destino}",
                    'codigo' => $row->codigo,
                    'viajes' => (int) $row->viajes,
                    'boletos_emitidos' => (int) $row->boletos_emitidos,
                    'ocupacion_porcentaje' => round(((int) $row->boletos_emitidos / $capacidad) * 100, 2),
                ];
            });

        return $this->success($data, 'Ocupacion por ruta.');
    }

    public function abordajesPorViaje(Request $request)
    {
        $query = EventoAbordaje::query()
            ->select('viaje_id', 'resultado', DB::raw('COUNT(*) as total'))
            ->with('viaje.ruta')
            ->groupBy('viaje_id', 'resultado');

        if ($request->filled('viaje_id')) {
            $query->where('viaje_id', $request->integer('viaje_id'));
        }

        return $this->success($query->get(), 'Estadisticas de abordaje.');
    }

    public function auditoria(Request $request)
    {
        $logs = AuditLog::query()
            ->with('usuario:id,nombre,email,rol')
            ->when($request->filled('accion'), fn ($q) => $q->where('accion', 'like', '%' . $request->string('accion') . '%'))
            ->when($request->filled('usuario_id'), fn ($q) => $q->where('usuario_id', $request->integer('usuario_id')))
            ->when($request->filled('desde'), fn ($q) => $q->whereDate('created_at', '>=', $request->date('desde')))
            ->when($request->filled('hasta'), fn ($q) => $q->whereDate('created_at', '<=', $request->date('hasta')))
            ->latest('id')
            ->paginate((int) $request->integer('per_page', 25));

        return $this->success($logs, 'Log de auditoria.');
    }

    public function ingresos(Request $request)
    {
        $desde = $request->date('desde') ?: now()->startOfMonth();
        $hasta = $request->date('hasta') ?: now();

        $ingresos = Boleto::query()
            ->selectRaw('DATE(fecha_emision) as fecha')
            ->selectRaw('COUNT(*) as boletos')
            ->selectRaw('SUM(precio_final) as ingresos')
            ->whereIn('estado', ['pagado', 'abordado'])
            ->whereBetween('fecha_emision', [$desde->startOfDay(), $hasta->endOfDay()])
            ->groupByRaw('DATE(fecha_emision)')
            ->orderBy('fecha')
            ->get();

        return $this->success($ingresos, 'Ingresos por periodo.');
    }
}
