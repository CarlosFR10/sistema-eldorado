<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asiento extends Model
{
    use HasFactory;

    protected $table = 'asientos';

    protected $fillable = [
        'viaje_id',
        'numero',
        'fila',
        'columna',
        'piso',
        'tipo',
        'estado',
        'bloqueado_hasta',
    ];

    protected $casts = [
        'bloqueado_hasta' => 'datetime',
    ];

    public function viaje()
    {
        return $this->belongsTo(Viaje::class, 'viaje_id');
    }

    public function boleto()
    {
        return $this->hasOne(Boleto::class, 'asiento_id');
    }
}
