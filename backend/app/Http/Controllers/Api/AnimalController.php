<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Animal;
use App\Models\Farm;
use Illuminate\Http\Request;

class AnimalController extends Controller
{
    /**
     * Listar animales de una finca específica.
     */
    public function index(Request $request, $farmId)
    {
        $farm = Farm::findOrFail($farmId);

        if ($farm->user_id !== $request->user()->id && !$request->user()->hasSharedAccess($farmId)) {
            return response()->json(['mensaje' => 'No autorizado'], 403);
        }

        $animals = $farm->animals()->with('images', 'weightRecords')->get();

        return response()->json([
            'mensaje' => 'Animales obtenidos exitosamente',
            'datos' => $animals
        ]);
    }

    /**
     * Registrar un nuevo animal.
     */
    public function store(Request $request)
    {
        $request->validate([
            'farm_id' => 'required|exists:farms,id',
            'arete' => 'required|string|unique:animals',
            'nombre' => 'nullable|string|max:255',
            'raza' => 'nullable|string|max:255',
            'fecha_nacimiento' => 'nullable|date',
            'genero' => 'required|in:Macho,Hembra',
            'proposito' => 'nullable|string|max:255',
            'peso_actual' => 'nullable|numeric|min:0',
            'notas' => 'nullable|string',
        ]);

        $farm = Farm::findOrFail($request->farm_id);

        if ($farm->user_id !== $request->user()->id) {
            return response()->json(['mensaje' => 'No autorizado'], 403);
        }

        $animal = Animal::create($request->all());

        return response()->json([
            'mensaje' => 'Animal registrado exitosamente',
            'datos' => $animal
        ], 201);
    }

    /**
     * Mostrar un animal específico.
     */
    public function show(Request $request, Animal $animal)
    {
        if ($animal->farm->user_id !== $request->user()->id && !$request->user()->hasSharedAccess($animal->farm_id)) {
            return response()->json(['mensaje' => 'No autorizado'], 403);
        }

        $animal->load('images', 'weightRecords');

        return response()->json([
            'mensaje' => 'Animal obtenido exitosamente',
            'datos' => $animal
        ]);
    }

    /**
     * Actualizar datos de un animal.
     */
    public function update(Request $request, Animal $animal)
    {
        if ($animal->farm->user_id !== $request->user()->id) {
            return response()->json(['mensaje' => 'No autorizado'], 403);
        }

        $request->validate([
            'arete' => 'sometimes|required|string|unique:animals,arete,' . $animal->id,
            'nombre' => 'nullable|string|max:255',
            'raza' => 'nullable|string|max:255',
            'fecha_nacimiento' => 'nullable|date',
            'genero' => 'sometimes|required|in:Macho,Hembra',
            'proposito' => 'nullable|string|max:255',
            'peso_actual' => 'nullable|numeric|min:0',
            'notas' => 'nullable|string',
        ]);

        $animal->update($request->all());

        return response()->json([
            'mensaje' => 'Animal actualizado exitosamente',
            'datos' => $animal
        ]);
    }

    /**
     * Eliminar un animal.
     */
    public function destroy(Request $request, Animal $animal)
    {
        if ($animal->farm->user_id !== $request->user()->id) {
            return response()->json(['mensaje' => 'No autorizado'], 403);
        }

        $animal->delete();

        return response()->json([
            'mensaje' => 'Animal eliminado exitosamente'
        ]);
    }
}
