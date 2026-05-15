<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UbicacionGps extends Model
{
    use HasFactory;

    protected $table = 'ubicaciones_gps';

    protected $fillable = [
        'bus_id',
        'viaje_id',
        'latitud',
        'longitud',
        'velocidad',
        'rumbo',
        'altitud',
        'precision_m',
        'signal_loss',
        'timestamp',
    ];

    public $timestamps = false;
    public const CREATED_AT = 'timestamp';

    protected $casts = [
        'latitud' => 'decimal:7',
        'longitud' => 'decimal:7',
        'velocidad' => 'decimal:1',
        'rumbo' => 'decimal:1',
        'altitud' => 'decimal:1',
        'precision_m' => 'decimal:1',
        'timestamp' => 'datetime',
    ];

    public function bus()
    {
        return $this->belongsTo(Bus::class, 'bus_id');
    }

    public function viaje()
    {
        return $this->belongsTo(Viaje::class, 'viaje_id');
    }
}
