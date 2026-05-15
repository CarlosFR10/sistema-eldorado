<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conductor extends Model
{
    use HasFactory;

    protected $table = 'conductores';

    protected $fillable = [
        'usuario_id',
        'licencia',
        'categoria',
        'vencimiento_lic',
    ];

    public $timestamps = false;

    protected $casts = [
        'vencimiento_lic' => 'date',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function viajes()
    {
        return $this->hasMany(Viaje::class, 'conductor_id');
    }
}
