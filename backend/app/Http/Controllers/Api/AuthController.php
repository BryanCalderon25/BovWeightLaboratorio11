<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Registro de usuario.
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'mensaje' => 'Usuario registrado exitosamente',
            'datos' => $user,
            'token_acceso' => $token,
            'tipo_token' => 'Bearer',
        ], 201);
    }

    /**
     * Inicio de sesión.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return response()->json(['mensaje' => 'El usuario no existe en la base de datos.'], 404);
        }

        if (! Hash::check($request->password, $user->password)) {
            return response()->json(['mensaje' => 'La contraseña es incorrecta para este usuario.'], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'mensaje' => 'Inicio de sesión exitoso',
            'datos' => $user,
            'token_acceso' => $token,
            'tipo_token' => 'Bearer',
        ]);
    }

    /**
     * Perfil del usuario autenticado.
     */
    public function profile(Request $request)
    {
        return response()->json([
            'mensaje' => 'Perfil obtenido exitosamente',
            'datos' => $request->user(),
        ]);
    }

    /**
     * Cerrar sesión.
     */
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'mensaje' => 'Sesión cerrada exitosamente y tokens revocados'
        ]);
    }
}
