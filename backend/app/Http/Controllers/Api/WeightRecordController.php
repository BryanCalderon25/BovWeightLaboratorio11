<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WeightRecord;
use App\Models\Animal;
use Illuminate\Http\Request;

class WeightRecordController extends Controller
{
    public function listAll(Request $request)
    {
        $user = $request->user();
        
        $query = WeightRecord::query();

        if ($user->invited_farm_id && (!$user->guest_expires_at || now()->lt($user->guest_expires_at))) {
            $query->whereHas('animal', function ($q) use ($user) {
                $q->where('farm_id', $user->invited_farm_id);
            });
        } else {
            $query->whereHas('animal.farm', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }
        
        $records = $query->with(['animal.farm'])
            ->orderBy('fecha_pesaje', 'desc')
            ->get();

        return response()->json([
            'mensaje' => 'Todos los registros de pesaje obtenidos exitosamente',
            'datos' => $records
        ]);
    }

    /**
     * Obtener el historial de pesaje de un animal.
     */
    public function index(Request $request, $animalId)
    {
        $animal = Animal::findOrFail($animalId);

        if ($animal->farm->user_id !== $request->user()->id && !$request->user()->hasSharedAccess($animal->farm_id)) {
            return response()->json(['mensaje' => 'No autorizado'], 403);
        }

        $records = $animal->weightRecords()->orderBy('fecha_pesaje', 'desc')->get();

        return response()->json([
            'mensaje' => 'Historial de pesaje obtenido exitosamente',
            'datos' => $records
        ]);
    }

    /**
     * Registrar un pesaje manualmente (offline sync o manual).
     */
    public function store(Request $request)
    {
        $request->validate([
            'animal_id' => 'required|exists:animals,id',
            'peso_estimado' => 'required|numeric|min:0',
            'fecha_pesaje' => 'required|date',
            'notas' => 'nullable|string',
        ]);

        $animal = Animal::findOrFail($request->animal_id);

        if ($animal->farm->user_id !== $request->user()->id) {
            return response()->json(['mensaje' => 'No autorizado'], 403);
        }

        $record = WeightRecord::create([
            'animal_id' => $animal->id,
            'peso_estimado' => $request->peso_estimado,
            'fecha_pesaje' => $request->fecha_pesaje,
            'notas' => $request->notas ?? 'Pesaje manual / Sincronización offline',
            'estado_sincronizacion' => 'sincronizado',
        ]);

        // Actualizar el peso actual del animal
        $animal->update(['peso_actual' => $request->peso_estimado]);

        return response()->json([
            'mensaje' => 'Registro de pesaje creado exitosamente',
            'datos' => $record
        ], 201);
    }
}
