<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RutaPunto extends Model
{
    protected $table = 'ruta_puntos';

    public $timestamps = false;

    protected $fillable = [
        'ruta_id',
        'orden',
        'latitud',
        'longitud',
        'nombre',
    ];

    protected $casts = [
        'latitud' => 'decimal:7',
        'longitud' => 'decimal:7',
        'orden' => 'integer',
    ];

    public function ruta(): BelongsTo
    {
        return $this->belongsTo(Ruta::class, 'ruta_id');
    }
}