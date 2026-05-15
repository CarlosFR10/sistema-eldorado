<?php

declare(strict_types=1);

namespace App\Http\Controllers\Viaje;

use App\Http\Controllers\Controller;
use App\Models\Bus;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BusController extends Controller
{
    public function index(Request $request)
    {
        $buses = Bus::query()
            ->when(!$request->boolean('incluir_inactivos'), fn ($q) => $q->where('activo', true))
            ->orderBy('placa')
            ->get();

        return $this->success($buses, 'Lista de buses.');
    }

    public function store(Request $request)
    {
        $bus = Bus::create($this->validatedData($request));

        return $this->success($bus, 'Bus registrado correctamente.', 201);
    }

    public function update(Request $request, int $id)
    {
        $bus = Bus::findOrFail($id);
        $bus->update($this->validatedData($request, $bus->id));

        return $this->success($bus->fresh(), 'Bus actualizado correctamente.');
    }

    public function croquis(int $id)
    {
        $bus = Bus::findOrFail($id);

        return $this->success([
            'bus_id' => $bus->id,
            'placa' => $bus->placa,
            'capacidad' => $bus->capacidad,
            'config_asientos' => $bus->config_asientos,
        ], 'Croquis del bus.');
    }

    private function validatedData(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'placa' => ['required', 'string', 'max:10', Rule::unique('buses', 'placa')->ignore($id)],
            'marca' => ['required', 'string', 'max:50'],
            'modelo' => ['required', 'string', 'max:50'],
            'anio' => ['required', 'integer', 'between:1990,2035'],
            'capacidad' => ['required', 'integer', 'between:1,80'],
            'tipo_bus' => ['required', Rule::in(['semicama', 'cama_completa', 'ejecutivo', 'doble_piso'])],
            'config_asientos' => ['required', 'array'],
            'gps_imei' => ['nullable', 'string', 'max:20'],
            'activo' => ['sometimes', 'boolean'],
        ]);
    }
}
