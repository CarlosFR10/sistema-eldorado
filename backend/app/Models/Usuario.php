<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Usuario extends Authenticatable implements JWTSubject
{
    use HasFactory;
    use Notifiable;
    use SoftDeletes;

    protected $table = 'usuarios';

    protected $fillable = [
        'nombre',
        'email',
        'password',
        'rol',
        'turno',
        'activo',
        'ultimo_acceso',
        'token_2fa',
        'expires_2fa',
    ];

    protected $hidden = [
        'password',
        'token_2fa',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'ultimo_acceso' => 'datetime',
        'expires_2fa' => 'datetime',
    ];

    public function getJWTIdentifier(): mixed
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [
            'rol' => $this->rol,
            'turno' => $this->turno,
        ];
    }

    public function boletosVendidos()
    {
        return $this->hasMany(Boleto::class, 'vendedor_id');
    }

    public function conductor()
    {
        return $this->hasOne(Conductor::class, 'usuario_id');
    }
}
