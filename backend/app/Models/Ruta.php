<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ruta extends Model
{
    use HasFactory;

    protected $table = 'rutas';

    protected $fillable = [
        'codigo',
        'origen',
        'destino',
        'distancia_km',
        'duracion_horas',
        'precio_base',
        'activa',
        'paradas',
    ];

    protected $casts = [
        'distancia_km' => 'decimal:2',
        'duracion_horas' => 'decimal:1',
        'precio_base' => 'decimal:2',
        'activa' => 'boolean',
        'paradas' => 'array',
    ];

    public function viajes()
    {
        return $this->hasMany(Viaje::class, 'ruta_id');
    }
}
