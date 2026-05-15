<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventoAbordaje extends Model
{
    use HasFactory;

    protected $table = 'eventos_abordaje';

    protected $fillable = [
        'boleto_id',
        'viaje_id',
        'pasajero_id',
        'operador_id',
        'tipo_validacion',
        'resultado',
        'ip_dispositivo',
        'latitud',
        'longitud',
    ];

    public $timestamps = false;
    public const CREATED_AT = 'created_at';

    protected $casts = [
        'created_at' => 'datetime',
        'latitud' => 'decimal:7',
        'longitud' => 'decimal:7',
    ];

    public function boleto()
    {
        return $this->belongsTo(Boleto::class, 'boleto_id');
    }

    public function viaje()
    {
        return $this->belongsTo(Viaje::class, 'viaje_id');
    }

    public function pasajero()
    {
        return $this->belongsTo(Pasajero::class, 'pasajero_id');
    }

    public function operador()
    {
        return $this->belongsTo(Usuario::class, 'operador_id');
    }
}
