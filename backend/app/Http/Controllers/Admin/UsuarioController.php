<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UsuarioController extends Controller
{
    public function index(Request $request)
    {
        $usuarios = Usuario::query()
            ->when($request->filled('rol'), fn ($q) => $q->where('rol', $request->string('rol')))
            ->when($request->filled('activo'), fn ($q) => $q->where('activo', $request->boolean('activo')))
            ->when($request->filled('q'), function ($q) use ($request): void {
                $term = '%' . $request->string('q') . '%';
                $q->where(fn ($inner) => $inner->where('nombre', 'like', $term)->orWhere('email', 'like', $term));
            })
            ->latest('id')
            ->paginate((int) $request->integer('per_page', 20));

        return $this->success($usuarios, 'Lista de usuarios.');
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);
        $data['password'] = Hash::make($data['password']);

        $usuario = Usuario::create($data);

        return $this->success($usuario, 'Usuario creado correctamente.', 201);
    }

    public function show(int $id)
    {
        return $this->success(Usuario::findOrFail($id), 'Detalle del usuario.');
    }

    public function update(Request $request, int $id)
    {
        $usuario = Usuario::findOrFail($id);
        $data = $this->validatedData($request, $usuario->id, false);

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $usuario->update($data);

        return $this->success($usuario->fresh(), 'Usuario actualizado correctamente.');
    }

    public function destroy(int $id)
    {
        $usuario = Usuario::findOrFail($id);
        $usuario->update(['activo' => false]);
        $usuario->delete();

        return $this->success(null, 'Usuario desactivado correctamente.');
    }

    private function validatedData(Request $request, ?int $id = null, bool $passwordRequired = true): array
    {
        return $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', Rule::unique('usuarios', 'email')->ignore($id)],
            'password' => [$passwordRequired ? 'required' : 'nullable', 'string', 'min:8', 'max:255'],
            'rol' => ['required', Rule::in(['administrador', 'supervisor', 'vendedor', 'auxiliar', 'conductor', 'autoridad'])],
            'turno' => ['nullable', Rule::in(['manana', 'tarde', 'noche'])],
            'activo' => ['sometimes', 'boolean'],
        ]);
    }
}
