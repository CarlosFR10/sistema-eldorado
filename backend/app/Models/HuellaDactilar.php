<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HuellaDactilar extends Model
{
    use HasFactory;

    protected $table = 'huellas_dactilares';

    protected $fillable = [
        'pasajero_id',
        'plantilla',
        'dedo',
        'calidad',
        'registrado_por',
    ];

    public $timestamps = false;

    protected $casts = [
        'calidad' => 'integer',
    ];

    public function pasajero()
    {
        return $this->belongsTo(Pasajero::class, 'pasajero_id');
    }

    public function registrador()
    {
        return $this->belongsTo(Usuario::class, 'registrado_por');
    }
}