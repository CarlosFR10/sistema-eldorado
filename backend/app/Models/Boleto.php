<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Boleto extends Model
{
    use HasFactory;

    protected $table = 'boletos';

    protected $fillable = [
        'codigo_boleto',
        'viaje_id',
        'pasajero_id',
        'asiento_id',
        'vendedor_id',
        'precio',
        'descuento',
        'precio_final',
        'metodo_pago',
        'estado',
        'qr_payload',
        'qr_imagen',
        'es_menor',
        'adulto_resp_id',
        'fecha_emision',
        'fecha_vencimiento',
    ];

    protected $casts = [
        'precio' => 'decimal:2',
        'descuento' => 'decimal:2',
        'precio_final' => 'decimal:2',
        'es_menor' => 'boolean',
        'fecha_emision' => 'datetime',
        'fecha_vencimiento' => 'datetime',
    ];

    public function viaje()
    {
        return $this->belongsTo(Viaje::class, 'viaje_id');
    }

    public function pasajero()
    {
        return $this->belongsTo(Pasajero::class, 'pasajero_id');
    }

    public function asiento()
    {
        return $this->belongsTo(Asiento::class, 'asiento_id');
    }

    public function vendedor()
    {
        return $this->belongsTo(Usuario::class, 'vendedor_id');
    }

    public function adultoResponsable()
    {
        return $this->belongsTo(Pasajero::class, 'adulto_resp_id');
    }

    public function eventosAbordaje()
    {
        return $this->hasMany(EventoAbordaje::class, 'boleto_id');
    }
}