<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Farm;
use App\Models\Animal;
use App\Models\WeightRecord;
use Carbon\Carbon;

class FarmAndAnimalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ganadero = User::where('email', 'ganadero@prueba.com')->first();

        if (!$ganadero) return;

        // Crear Fincas
        $finca1 = Farm::create([
            'user_id' => $ganadero->id,
            'nombre' => 'Finca La Esperanza',
            'ubicacion' => 'San Carlos, Alajuela, Costa Rica',
            'descripcion' => 'Finca dedicada al ganado de carne y doble propósito.',
            'area_hectareas' => 150.5,
        ]);

        $finca2 = Farm::create([
            'user_id' => $ganadero->id,
            'nombre' => 'Hacienda El Buen Pastor',
            'ubicacion' => 'Pococí, Limón, Costa Rica',
            'descripcion' => 'Finca lechera con ganado de alta genética.',
            'area_hectareas' => 85.0,
        ]);

        // Crear Animales para Finca 1
        $animal1 = Animal::create([
            'farm_id' => $finca1->id,
            'arete' => 'CR-1001',
            'nombre' => 'Toro Bravo',
            'raza' => 'Brahman',
            'fecha_nacimiento' => Carbon::now()->subYears(3)->format('Y-m-d'),
            'genero' => 'Macho',
            'proposito' => 'Carne',
            'peso_actual' => 550.0,
            'notas' => 'Excelente semental.',
        ]);

        $animal2 = Animal::create([
            'farm_id' => $finca1->id,
            'arete' => 'CR-1002',
            'nombre' => 'La Pinta',
            'raza' => 'Gyr',
            'fecha_nacimiento' => Carbon::now()->subYears(4)->format('Y-m-d'),
            'genero' => 'Hembra',
            'proposito' => 'Doble propósito',
            'peso_actual' => 420.5,
            'notas' => 'Vaca productora.',
        ]);

        // Crear Registros de Pesaje para Animal 1
        WeightRecord::create([
            'animal_id' => $animal1->id,
            'peso_estimado' => 500.0,
            'fecha_pesaje' => Carbon::now()->subMonths(6)->format('Y-m-d'),
            'estado_sincronizacion' => 'sincronizado',
            'notas' => 'Pesaje manual.',
        ]);

        WeightRecord::create([
            'animal_id' => $animal1->id,
            'peso_estimado' => 550.0,
            'fecha_pesaje' => Carbon::now()->format('Y-m-d'),
            'estado_sincronizacion' => 'sincronizado',
            'notas' => 'Pesaje estimado con YOLOv8.',
        ]);
    }
}
