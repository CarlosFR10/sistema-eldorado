<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Viaje extends Model
{
    use HasFactory;

    protected $table = 'viajes';

    protected $fillable = [
        'codigo_viaje',
        'ruta_id',
        'bus_id',
        'conductor_id',
        'vendedor_id',
        'fecha_salida',
        'fecha_llegada_est',
        'fecha_llegada_real',
        'precio_final',
        'estado',
        'observaciones',
        'simulacion_llamada_actual',
        'simulacion_llamadas_totales',
        'simulacion_progreso',
        'simulacion_waypoints',
        'simulacion_inicio',
    ];

    protected $casts = [
        'fecha_salida' => 'datetime',
        'fecha_llegada_est' => 'datetime',
        'fecha_llegada_real' => 'datetime',
        'precio_final' => 'decimal:2',
        'simulacion_progreso' => 'float',
        'simulacion_llamada_actual' => 'integer',
        'simulacion_llamadas_totales' => 'integer',
        'simulacion_waypoints' => 'array',
        'simulacion_inicio' => 'datetime',
    ];

    public function ruta()
    {
        return $this->belongsTo(Ruta::class, 'ruta_id');
    }

    public function bus()
    {
        return $this->belongsTo(Bus::class, 'bus_id');
    }

    public function conductor()
    {
        return $this->belongsTo(Conductor::class, 'conductor_id');
    }

    public function vendedor()
    {
        return $this->belongsTo(Usuario::class, 'vendedor_id');
    }

    public function asientos()
    {
        return $this->hasMany(Asiento::class, 'viaje_id')->orderBy('numero');
    }

    public function boletos()
    {
        return $this->hasMany(Boleto::class, 'viaje_id');
    }

    public function ubicacionesGps()
    {
        return $this->hasMany(UbicacionGps::class, 'viaje_id')->orderBy('timestamp');
    }
}
