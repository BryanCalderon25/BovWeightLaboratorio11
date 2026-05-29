<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Animal;
use App\Models\WeightRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class MLIntegrationController extends Controller
{
    /**
     * Procesar imagen y obtener estimación de peso desde el microservicio.
     */
    public function predictWeight(Request $request)
    {
        $request->validate([
            'animal_id' => 'required|exists:animals,id',
            'image' => 'required|file|max:10240',
            'raza' => 'nullable|string'
        ]);

        $animal = Animal::findOrFail($request->animal_id);

        if ($animal->farm->user_id !== $request->user()->id) {
            return response()->json(['mensaje' => 'No autorizado'], 403);
        }

        // Subir la imagen al servidor (storage)
        $path = $request->file('image')->store('animal_images', 'public');

        // Guardar registro de imagen en la base de datos
        $animalImage = $animal->images()->create([
            'ruta_imagen' => $path,
            'tipo' => 'lateral',
        ]);

        try {
            $mlServiceUrl = config('services.ml.url');
            
            // Calcular edad en meses si tiene fecha de nacimiento
            $edadMeses = null;
            if ($animal->fecha_nacimiento) {
                $edadMeses = \Carbon\Carbon::parse($animal->fecha_nacimiento)->diffInMonths(now());
            }

            $response = Http::attach(
                'image', 
                file_get_contents($request->file('image')->getRealPath()), 
                $request->file('image')->getClientOriginalName()
            )
            ->attach('raza', $request->raza ?? $animal->raza ?? 'Desconocida')
            ->attach('genero', $animal->genero ?? 'Hembra')
            ->attach('peso_actual', (string)($animal->peso_actual ?? ''))
            ->attach('edad_meses', (string)($edadMeses ?? ''))
            ->post("{$mlServiceUrl}/api/estimate");

            if ($response->successful()) {
                $data = $response->json();
                
                $pesoEstimado = $data['peso_estimado_kg'] ?? 0;
                
                // Guardar el registro de pesaje
                $weightRecord = WeightRecord::create([
                    'animal_id' => $animal->id,
                    'peso_estimado' => $pesoEstimado,
                    'fecha_pesaje' => now(),
                    'animal_image_id' => $animalImage->id,
                    'datos_modelo' => $data,
                    'estado_sincronizacion' => 'sincronizado',
                    'notas' => 'Pesaje estimado por modelo YOLOv8 - Biometría Virtual',
                ]);

                // Actualizar peso actual del animal
                $animal->update(['peso_actual' => $pesoEstimado]);

                return response()->json([
                    'mensaje' => 'Pesaje estimado exitosamente',
                    'datos' => [
                        'registro' => $weightRecord,
                        'detalles_modelo' => $data
                    ],
                    'disclaimer' => 'La estimación de peso es aproximada y depende de la calidad de la imagen.'
                ]);
            }

            return response()->json([
                'mensaje' => 'Error al comunicarse con el servicio de estimación.',
                'detalles' => $response->body()
            ], 500);

        } catch (\Exception $e) {
            return response()->json([
                'mensaje' => 'Excepción al procesar la estimación de peso.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
