<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WeightRecord;
use App\Models\Animal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OfflineSyncController extends Controller
{
    /**
     * Sincronizar un lote de registros de pesaje creados offline.
     */
    public function syncWeightRecords(Request $request)
    {
        $request->validate([
            'registros' => 'required|array',
            'registros.*.animal_id' => 'required|exists:animals,id',
            'registros.*.peso_estimado' => 'required|numeric|min:0',
            'registros.*.fecha_pesaje' => 'required|date',
            'registros.*.notas' => 'nullable|string',
        ]);

        $registros = $request->input('registros');
        $sincronizados = [];
        $errores = [];

        DB::beginTransaction();

        try {
            foreach ($registros as $registroData) {
                $animal = Animal::find($registroData['animal_id']);

                if ($animal->farm->user_id !== $request->user()->id) {
                    $errores[] = [
                        'animal_id' => $registroData['animal_id'],
                        'mensaje' => 'No autorizado para este animal',
                    ];
                    continue;
                }

                $record = WeightRecord::create([
                    'animal_id' => $animal->id,
                    'peso_estimado' => $registroData['peso_estimado'],
                    'fecha_pesaje' => $registroData['fecha_pesaje'],
                    'notas' => $registroData['notas'] ?? 'Sincronización offline',
                    'estado_sincronizacion' => 'sincronizado',
                ]);

                // Actualizar el peso actual del animal
                $animal->update(['peso_actual' => $registroData['peso_estimado']]);

                $sincronizados[] = $record;
            }

            DB::commit();

            return response()->json([
                'mensaje' => 'Sincronización completada',
                'sincronizados' => count($sincronizados),
                'errores' => count($errores),
                'detalles_errores' => $errores
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'mensaje' => 'Error durante la sincronización',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
