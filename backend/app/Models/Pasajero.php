<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Pasajero extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'pasajeros';

    protected $fillable = [
        'nombres',
        'apellidos',
        'numero_ci',
        'complemento_ci',
        'expedido_en',
        'fecha_nacimiento',
        'telefono',
        'email',
        'tiene_huella',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
        'tiene_huella' => 'boolean',
    ];

    protected $appends = [
        'edad',
        'es_menor_calculado',
    ];

    public function getEdadAttribute(): int
    {
        return Carbon::parse($this->fecha_nacimiento)->age;
    }

    public function getEsMenorCalculadoAttribute(): bool
    {
        return $this->edad < 18;
    }

    public function huellas()
    {
        return $this->hasMany(HuellaDactilar::class, 'pasajero_id');
    }

    public function boletos()
    {
        return $this->hasMany(Boleto::class, 'pasajero_id');
    }

    public function adultoResponsable()
    {
        return $this->hasOne(MenorAdultoResponsable::class, 'menor_id')->latest('id');
    }

    public function menoresResponsables()
    {
        return $this->hasMany(MenorAdultoResponsable::class, 'adulto_responsable_id');
    }
}
