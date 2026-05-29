<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Farm;
use App\Models\FarmInvitation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FarmInvitationController extends Controller
{
    /**
     * Generar un enlace de invitación temporal para una finca.
     */
    public function store(Request $request)
    {
        $request->validate([
            'farm_id' => 'required|exists:farms,id',
            'role' => 'required|in:veterinario,comprador',
            'expires_in_hours' => 'nullable|integer|min:1|max:168', // Máximo 1 semana
        ]);

        $farm = Farm::findOrFail($request->farm_id);

        // Validar que el usuario autenticado sea el dueño de la finca
        if ($farm->user_id !== $request->user()->id) {
            return response()->json(['mensaje' => 'No autorizado'], 403);
        }

        $hours = $request->input('expires_in_hours', 24);
        $expiresAt = now()->addHours($hours);
        $token = Str::random(40);

        $invitation = FarmInvitation::create([
            'farm_id' => $farm->id,
            'role' => $request->role,
            'token' => $token,
            'expires_at' => $expiresAt,
            'max_uses' => 5, // Límite de 5 usos por seguridad
            'uses_count' => 0,
        ]);

        // Enlace para la aplicación Ionic móvil (por defecto en el puerto 8100 o 5173 según esté corriendo)
        $invitationUrl = 'http://localhost:8100/invitado/acceso/' . $token;

        return response()->json([
            'mensaje' => 'Invitación generada exitosamente',
            'datos' => [
                'id' => $invitation->id,
                'role' => $invitation->role,
                'expires_at' => $invitation->expires_at->toIso8601String(),
                'token' => $invitation->token,
                'enlace' => $invitationUrl,
            ]
        ], 201);
    }

    /**
     * Resolver la invitación e iniciar sesión de invitado.
     */
    public function resolveGuestAccess(Request $request, $token)
    {
        $invitation = FarmInvitation::where('token', $token)->first();

        if (!$invitation || !$invitation->isValid()) {
            return response()->json([
                'mensaje' => 'El enlace de invitación no es válido o ha expirado.'
            ], 410);
        }

        // Incrementar contador de uso
        $invitation->increment('uses_count');

        // Generar un correo electrónico único y seguro de invitado en base al token
        $email = 'invitado_' . substr($token, 0, 10) . '@bovweight.cr';
        
        // Crear o recuperar el usuario invitado temporal
        $guest = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => 'Invitado (' . ucfirst($invitation->role) . ')',
                'password' => bcrypt(Str::random(24)),
                'invited_farm_id' => $invitation->farm_id,
                'guest_expires_at' => $invitation->expires_at,
            ]
        );

        // Asegurar que el usuario invitado temporal apunte a la finca correcta y mantenga su expiración
        $guest->update([
            'invited_farm_id' => $invitation->farm_id,
            'guest_expires_at' => $invitation->expires_at,
        ]);

        // Asignar rol de Spatie de manera segura
        try {
            $guest->assignRole('invitado');
        } catch (\Exception $e) {
            // Continuar si no está inicializado Spatie en la DB
        }

        // Generar Token de Sanctum
        $tokenResult = $guest->createToken('guest_token');

        return response()->json([
            'mensaje' => 'Acceso de invitado concedido exitosamente',
            'datos' => [
                'token' => $tokenResult->plainTextToken,
                'usuario' => [
                    'id' => $guest->id,
                    'name' => $guest->name,
                    'email' => $guest->email,
                    'role' => 'invitado',
                    'guest_role' => $invitation->role,
                    'invited_farm_id' => $invitation->farm_id,
                ],
                'finca_id' => $invitation->farm_id,
                'expiracion' => $invitation->expires_at->toIso8601String(),
            ]
        ]);
    }
}
