<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Farm;
use Illuminate\Http\Request;

class FarmController extends Controller
{
    /**
     * Listar fincas del usuario autenticado.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $isGuest = $user->invited_farm_id && (!$user->guest_expires_at || now()->lt($user->guest_expires_at));

        if ($isGuest) {
            $farms = Farm::where('id', $user->invited_farm_id)->with('animals')->get();
        } else {
            $farms = $user->farms()->with('animals')->get();
        }

        return response()->json([
            'mensaje' => 'Fincas obtenidas exitosamente',
            'datos' => $farms
        ]);
    }

    /**
     * Crear una nueva finca.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'ubicacion' => 'nullable|string',
            'descripcion' => 'nullable|string',
            'area_hectareas' => 'nullable|numeric|min:0',
        ]);

        $farm = $request->user()->farms()->create($request->all());

        return response()->json([
            'mensaje' => 'Finca creada exitosamente',
            'datos' => $farm
        ], 201);
    }

    /**
     * Mostrar una finca específica.
     */
    public function show(Request $request, Farm $farm)
    {
        if ($farm->user_id !== $request->user()->id && !$request->user()->hasSharedAccess($farm->id)) {
            return response()->json(['mensaje' => 'No autorizado'], 403);
        }

        $farm->load('animals');

        return response()->json([
            'mensaje' => 'Finca obtenida exitosamente',
            'datos' => $farm
        ]);
    }

    /**
     * Actualizar una finca.
     */
    public function update(Request $request, Farm $farm)
    {
        if ($farm->user_id !== $request->user()->id) {
            return response()->json(['mensaje' => 'No autorizado'], 403);
        }

        $request->validate([
            'nombre' => 'sometimes|required|string|max:255',
            'ubicacion' => 'nullable|string',
            'descripcion' => 'nullable|string',
            'area_hectareas' => 'nullable|numeric|min:0',
        ]);

        $farm->update($request->all());

        return response()->json([
            'mensaje' => 'Finca actualizada exitosamente',
            'datos' => $farm
        ]);
    }

    /**
     * Eliminar una finca.
     */
    public function destroy(Request $request, Farm $farm)
    {
        if ($farm->user_id !== $request->user()->id) {
            return response()->json(['mensaje' => 'No autorizado'], 403);
        }

        $farm->delete();

        return response()->json([
            'mensaje' => 'Finca eliminada exitosamente'
        ]);
    }
}
