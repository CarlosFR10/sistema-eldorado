<?php

declare(strict_types=1);

namespace App\Http\Controllers\Viaje;

use App\Http\Controllers\Controller;
use App\Models\Ruta;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RutaController extends Controller
{
    public function index(Request $request)
    {
        $query = Ruta::query()
            ->when(!$request->boolean('incluir_inactivas'), fn ($q) => $q->where('activa', true));

        if ($request->filled('fecha') && $request->is('api/public/*')) {
            $fecha = $request->date('fecha');
            $query->whereHas('viajes', function ($q) use ($fecha) {
                $q->whereDate('fecha_salida', $fecha)
                  ->whereNotIn('estado', ['cancelado', 'completado']);
            });
        }

        $rutas = $query->orderBy('origen')->orderBy('destino')->get();

        return $this->success($rutas, 'Lista de rutas.');
    }

    public function store(Request $request)
    {
        $ruta = Ruta::create($this->validatedData($request));

        return $this->success($ruta, 'Ruta creada correctamente.', 201);
    }

    public function update(Request $request, int $id)
    {
        $ruta = Ruta::findOrFail($id);
        $ruta->update($this->validatedData($request, $ruta->id));

        return $this->success($ruta->fresh(), 'Ruta actualizada correctamente.');
    }

    private function validatedData(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'codigo' => ['required', 'string', 'max:20', Rule::unique('rutas', 'codigo')->ignore($id)],
            'origen' => ['required', 'string', 'max:100'],
            'destino' => ['required', 'string', 'max:100'],
            'distancia_km' => ['required', 'numeric', 'min:1'],
            'duracion_horas' => ['required', 'numeric', 'min:0.5'],
            'precio_base' => ['required', 'numeric', 'min:0'],
            'activa' => ['sometimes', 'boolean'],
            'paradas' => ['nullable', 'array'],
        ]);
    }
}
