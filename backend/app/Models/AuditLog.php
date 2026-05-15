<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    use HasFactory;

    protected $table = 'audit_logs';

    protected $fillable = [
        'usuario_id',
        'accion',
        'tabla_afectada',
        'registro_id',
        'datos_antes',
        'datos_despues',
        'ip',
        'user_agent',
    ];

    public $timestamps = false;
    public const CREATED_AT = 'created_at';

    protected $casts = [
        'datos_antes' => 'array',
        'datos_despues' => 'array',
        'created_at' => 'datetime',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}
