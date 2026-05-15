<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $usuario = Usuario::where('email', $credentials['email'])->first();

        if (!$usuario || !$usuario->activo || !Hash::check($credentials['password'], $usuario->password)) {
            return $this->error(
                'Credenciales incorrectas.',
                ['email' => ['Email o contrasena invalidos']],
                'AUTH_INVALID',
                401
            );
        }

        if (in_array($usuario->rol, ['administrador', 'supervisor'], true)) {
            $otp = (string) random_int(100000, 999999);
            $usuario->update([
                'token_2fa' => $otp,
                'expires_2fa' => now()->addMinutes(5),
            ]);

            return $this->success([
                'requires_2fa' => true,
                'otp_dev' => $otp,
                'email' => $usuario->email,
                'rol' => $usuario->rol,
            ], 'Codigo OTP generado para verificacion.');
        }

        $token = Auth::guard('api')->login($usuario);
        $usuario->update(['ultimo_acceso' => now()]);

        return $this->success([
            'token' => $token,
            'token_type' => 'bearer',
            'usuario' => $usuario->fresh(),
        ], 'Inicio de sesion exitoso.');
    }

    public function verify2fa(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'token_2fa' => ['required', 'digits:6'],
        ]);

        $usuario = Usuario::where('email', $data['email'])->first();

        if (
            !$usuario ||
            $usuario->token_2fa !== $data['token_2fa'] ||
            !$usuario->expires_2fa ||
            $usuario->expires_2fa->isPast()
        ) {
            return $this->error('Codigo OTP invalido o vencido.', [], 'AUTH_2FA_INVALID', 422);
        }

        $usuario->forceFill([
            'token_2fa' => null,
            'expires_2fa' => null,
            'ultimo_acceso' => now(),
        ])->save();

        return $this->success([
            'token' => Auth::guard('api')->login($usuario),
            'token_type' => 'bearer',
            'usuario' => $usuario->fresh(),
        ], 'Verificacion completada.');
    }

    public function refresh()
    {
        return $this->success([
            'token' => Auth::guard('api')->refresh(),
            'token_type' => 'bearer',
        ], 'Token renovado correctamente.');
    }

    public function me(Request $request)
    {
        return $this->success($request->user(), 'Perfil autenticado.');
    }
}
