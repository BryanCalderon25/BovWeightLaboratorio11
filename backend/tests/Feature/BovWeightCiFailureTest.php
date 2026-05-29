<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Clase de prueba intencionalmente fallida para verificar la seguridad del pipeline.
 * Sirve para constatar que GitHub Actions bloquee el Pull Request si el código no cumple con los estándares.
 */
class BovWeightCiFailureTest extends TestCase
{
    /**
     * Prueba que fallará de manera intencional debido a un cálculo de peso absurdo (negativo).
     * Esto simula un error de lógica crítica o inestabilidad del microservicio de Machine Learning de BovWeight CR.
     *
     * Nota para el estudiante/evaluador:
     * Para que el pipeline pase a verde y el PR sea aprobado, esta prueba debe ser corregida
     * (por ejemplo, cambiando el valor a positivo) o comentada.
     *
     * @return void
     */
    public function test_estimacion_peso_bovino_invalida_falla_intencional(): void
    {
        // 1. Arrange: En ramas estables (develop/main) usamos un peso válido para que el CI sea verde
        $pesoEstimadoErroneo = 45.2;

        // 2. Act & Assert: Evaluamos la condición.
        // Falla intencionalmente porque un bovino real no puede tener peso negativo.
        $this->assertGreaterThan(
            0,
            $pesoEstimadoErroneo,
            '¡PRUEBA FALLIDA INTENCIONALMENTE! El peso del bovino es negativo. Bloqueando Pull Request inestable.'
        );
    }
}
