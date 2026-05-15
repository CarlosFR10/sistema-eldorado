<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MenorAdultoResponsable extends Model
{
    use HasFactory;

    protected $table = 'menores_adultos_responsables';

    protected $fillable = [
        'menor_id',
        'adulto_responsable_id',
        'tipo_relacion',
        'numero_permiso_dna',
        'fecha_permiso',
        'verificado_manualmente',
        'verificado_por',
        'observaciones',
    ];

    public $timestamps = false;

    protected $casts = [
        'fecha_permiso' => 'date',
        'verificado_manualmente' => 'boolean',
    ];

    public function menor()
    {
        return $this->belongsTo(Pasajero::class, 'menor_id');
    }

    public function adultoResponsable()
    {
        return $this->belongsTo(Pasajero::class, 'adulto_responsable_id');
    }

    public function verificador()
    {
        return $this->belongsTo(Usuario::class, 'verificado_por');
    }
}
