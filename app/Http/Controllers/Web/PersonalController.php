<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class PersonalController extends Controller
{
    /**
     * Listado de usuarios con filtros y paginación.
     */
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%$search%")
                  ->orWhere('email', 'LIKE', "%$search%");
            });
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('personal.index', compact('users'));
    }

    /**
     * Mostrar formulario de creación de usuario.
     */
    public function create()
    {
        return view('personal.create');
    }

    /**
     * Guardar nuevo usuario.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'role'     => 'required|string|in:admin,operador',
            'password' => 'nullable|string|min:6',
        ]);

        // Si no viene contraseña, generar una temporal aleatoria
        $password = !empty($validated['password'])
            ? $validated['password']
            : substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%&'), 0, 10);

        User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($password),
            'role'     => $validated['role'],
        ]);

        $msg = !empty($validated['password'])
            ? 'Usuario creado correctamente.'
            : 'Usuario creado. Contraseña temporal: ' . $password;

        return redirect()->route('personal.index')->with('success', $msg);
    }

    /**
     * Mostrar formulario de edición.
     */
    public function edit(User $personal)
    {
        return view('personal.edit', compact('personal'));
    }

    /**
     * Actualizar usuario.
     */
    public function update(Request $request, User $personal)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => ['required', 'email', Rule::unique('users')->ignore($personal->id)],
            'role'     => 'required|string|in:admin,operador',
            'password' => 'nullable|string|min:6',
        ]);

        $personal->name  = $validated['name'];
        $personal->email = $validated['email'];
        $personal->role  = $validated['role'];

        if (!empty($validated['password'])) {
            $personal->password = Hash::make($validated['password']);
        }

        $personal->save();

        return redirect()->route('personal.index')->with('success', 'Usuario actualizado.');
    }

    /**
     * Eliminar usuario (soft delete o destroy).
     */
    public function destroy(User $personal)
    {
        $personal->delete();
        return redirect()->route('personal.index')->with('success', 'Usuario eliminado.');
    }
}