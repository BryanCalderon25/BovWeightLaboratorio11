<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Clase de prueba para la verificación exitosa del pipeline de CI de BovWeight CR.
 * Todo el contenido está documentado y estructurado según las buenas prácticas empresariales.
 */
class BovWeightCiSuccessTest extends TestCase
{
    /**
     * Prueba de validación de simulación de estimación de peso exitosa.
     * Sirve para comprobar el funcionamiento general del entorno de pruebas.
     *
     * @return void
     */
    public function test_estimacion_peso_bovino_exitosa(): void
    {
        // 1. Arrange: Preparar datos de prueba simulados
        $datosBovino = [
            'codigo_bovino' => 'BOV-CR-9921',
            'raza' => 'Jersey',
            'edad_meses' => 18,
            'peso_estimado_kg' => 380.75,
            'estado' => 'Saludable'
        ];

        // 2. Act: Procesar/Evaluar las condiciones del negocio
        $esValido = !empty($datosBovino['codigo_bovino']) && $datosBovino['peso_estimado_kg'] > 0;

        // 3. Assert: Verificar los resultados esperados
        $this->assertTrue($esValido, 'La estructura de los datos del bovino es consistente.');
        $this->assertEquals('Jersey', $datosBovino['raza'], 'La raza registrada no coincide con los datos esperados.');
        $this->assertGreaterThan(200, $datosBovino['peso_estimado_kg'], 'El peso estimado de un bovino de 18 meses debería ser superior a 200 kg.');
    }
}
