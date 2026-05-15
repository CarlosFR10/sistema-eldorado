<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bus extends Model
{
    use HasFactory;

    protected $table = 'buses';

    protected $fillable = [
        'placa',
        'marca',
        'modelo',
        'anio',
        'capacidad',
        'tipo_bus',
        'config_asientos',
        'gps_imei',
        'activo',
    ];

    protected $casts = [
        'anio' => 'integer',
        'capacidad' => 'integer',
        'config_asientos' => 'array',
        'activo' => 'boolean',
    ];

    public function viajes()
    {
        return $this->hasMany(Viaje::class, 'bus_id');
    }

    public function ubicacionesGps()
    {
        return $this->hasMany(UbicacionGps::class, 'bus_id');
    }
}
